<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;

class LinuxController extends Controller
{
    public function index()
    {
        // Get Linux system information
        $systemInfo = $this->getSystemInfo();
        $diskUsage = $this->getDiskUsage();
        $memoryInfo = $this->getMemoryInfo();
        $cpuInfo = $this->getCpuInfo();
        $networkInfo = $this->getNetworkInfo();
        $processes = $this->getProcesses();
        
        return view('linux.index', compact('systemInfo', 'diskUsage', 'memoryInfo', 'cpuInfo', 'networkInfo', 'processes'));
    }

    public function getSystemInfo()
    {
        try {
            $osInfo = [
                'hostname' => gethostname(),
                'os' => php_uname('s'),
                'release' => php_uname('r'),
                'version' => php_uname('v'),
                'machine' => php_uname('m'),
                'current_user' => get_current_user(),
                'uptime' => $this->getUptime(),
                'load_average' => sys_getloadavg()
            ];
            
            return $osInfo;
        } catch (\Exception $e) {
            \Log::error('Error getting system info: ' . $e->getMessage());
            return ['error' => $e->getMessage()];
        }
    }

    public function getDiskUsage()
    {
        try {
            $disks = [];
            
            // Get disk usage for root partition
            $process = new Process(['df', '-h', '/']);
            $process->run();
            
            if ($process->isSuccessful()) {
                $output = $process->getOutput();
                $lines = explode("\n", trim($output));
                
                // Skip header line
                for ($i = 1; $i < count($lines); $i++) {
                    if (trim($lines[$i]) !== '') {
                        $parts = preg_split('/\s+/', $lines[$i]);
                        if (count($parts) >= 5) {
                            $disks[] = [
                                'filesystem' => $parts[0],
                                'size' => $parts[1],
                                'used' => $parts[2],
                                'available' => $parts[3],
                                'use_percent' => $parts[4],
                                'mounted_on' => $parts[5]
                            ];
                        }
                    }
                }
            }
            
            return $disks;
        } catch (\Exception $e) {
            \Log::error('Error getting disk usage: ' . $e->getMessage());
            return [['error' => $e->getMessage()]];
        }
    }

    public function getMemoryInfo()
    {
        try {
            $memory = [];
            
            $process = new Process(['free', '-h']);
            $process->run();
            
            if ($process->isSuccessful()) {
                $output = $process->getOutput();
                $lines = explode("\n", trim($output));
                
                foreach ($lines as $index => $line) {
                    if (trim($line) !== '') {
                        $parts = preg_split('/\s+/', trim($line));
                        
                        if ($index === 0) { // Header
                            $memory['headers'] = array_slice($parts, 0, 7);
                        } elseif (strpos($line, 'Mem:') === 0) {
                            $memory['mem'] = [
                                'total' => $parts[1],
                                'used' => $parts[2],
                                'free' => $parts[3],
                                'shared' => $parts[4],
                                'buff_cache' => $parts[5],
                                'available' => $parts[6]
                            ];
                        } elseif (strpos($line, 'Swap:') === 0) {
                            $memory['swap'] = [
                                'total' => $parts[1],
                                'used' => $parts[2],
                                'free' => $parts[3]
                            ];
                        }
                    }
                }
            }
            
            return $memory;
        } catch (\Exception $e) {
            \Log::error('Error getting memory info: ' . $e->getMessage());
            return ['error' => $e->getMessage()];
        }
    }

    public function getCpuInfo()
    {
        try {
            $cpuInfo = [];
            
            $process = new Process(['lscpu']);
            $process->run();
            
            if ($process->isSuccessful()) {
                $output = $process->getOutput();
                $lines = explode("\n", trim($output));
                
                foreach ($lines as $line) {
                    if (strpos($line, ':') !== false) {
                        $parts = explode(':', $line, 2);
                        $key = trim($parts[0]);
                        $value = trim($parts[1]);
                        
                        // Convert spaces and special chars to snake_case key
                        $snakeKey = strtolower(str_replace([' ', '-', '(', ')'], '_', $key));
                        $cpuInfo[$snakeKey] = $value;
                    }
                }
            }
            
            return $cpuInfo;
        } catch (\Exception $e) {
            \Log::error('Error getting CPU info: ' . $e->getMessage());
            return ['error' => $e->getMessage()];
        }
    }

    public function getNetworkInfo()
    {
        try {
            $network = [];
            
            $process = new Process(['ip', 'addr', 'show']);
            $process->run();
            
            if ($process->isSuccessful()) {
                $output = $process->getOutput();
                
                // Parse network interfaces
                $interfaces = [];
                $currentInterface = null;
                
                $lines = explode("\n", $output);
                foreach ($lines as $line) {
                    $line = trim($line);
                    
                    // Check if this line starts a new interface
                    if (preg_match('/^\d+:\s+([^\s:]+)/', $line, $matches)) {
                        $interfaceName = $matches[1];
                        $currentInterface = [
                            'name' => $interfaceName,
                            'details' => [$line],
                            'ipv4' => [],
                            'ipv6' => []
                        ];
                        $interfaces[] = $currentInterface;
                    } elseif ($currentInterface !== null) {
                        $currentInterface['details'][] = $line;
                        
                        // Extract IP addresses
                        if (preg_match('/inet\s+(\d+\.\d+\.\d+\.\d+\/\d+)/', $line, $ipMatches)) {
                            $currentInterface['ipv4'][] = $ipMatches[1];
                        }
                        if (preg_match('/inet6\s+([a-fA-F0-9:]+\/\d+)/', $line, $ipMatches)) {
                            $currentInterface['ipv6'][] = $ipMatches[1];
                        }
                    }
                }
                
                $network = $interfaces;
            }
            
            return $network;
        } catch (\Exception $e) {
            \Log::error('Error getting network info: ' . $e->getMessage());
            return ['error' => $e->getMessage()];
        }
    }

    public function getProcesses()
    {
        try {
            $processes = [];
            
            $process = new Process(['ps', 'aux', '--sort=-%cpu']);
            $process->run();
            
            if ($process->isSuccessful()) {
                $output = $process->getOutput();
                $lines = explode("\n", trim($output));
                
                // Get headers from first line
                $headers = preg_split('/\s+/', trim($lines[0]));
                
                // Process each line after the header
                for ($i = 1; $i < min(count($lines), 21); $i++) { // Limit to top 20 processes
                    if (trim($lines[$i]) !== '') {
                        $parts = preg_split('/\s+/', trim($lines[$i]), count($headers));
                        
                        if (count($parts) >= count($headers)) {
                            $proc = [];
                            foreach ($headers as $index => $header) {
                                $proc[strtolower($header)] = $parts[$index];
                            }
                            $processes[] = $proc;
                        }
                    }
                }
            }
            
            return $processes;
        } catch (\Exception $e) {
            \Log::error('Error getting processes: ' . $e->getMessage());
            return ['error' => $e->getMessage()];
        }
    }

    public function getUptime()
    {
        try {
            if (file_exists('/proc/uptime')) {
                $uptime = file_get_contents('/proc/uptime');
                $uptime = explode(' ', $uptime)[0];
                $uptime = floatval($uptime);
                
                $days = floor($uptime / 86400);
                $hours = floor(($uptime % 86400) / 3600);
                $minutes = floor(($uptime % 3600) / 60);
                
                return sprintf('%d days, %d hours, %d minutes', $days, $hours, $minutes);
            }
            
            return 'Unknown';
        } catch (\Exception $e) {
            \Log::error('Error getting uptime: ' . $e->getMessage());
            return 'Error';
        }
    }

    public function runCommand(Request $request)
    {
        $request->validate([
            'command' => 'required|string|max:255'
        ]);

        $command = $request->input('command');
        
        // Only allow safe commands
        $allowedPrefixes = [
            'ls', 'pwd', 'whoami', 'date', 'ps', 'top', 'free', 'df', 
            'du', 'cat', 'head', 'tail', 'grep', 'find', 'echo', 'uname'
        ];
        
        $isValidCommand = false;
        foreach ($allowedPrefixes as $prefix) {
            if (stripos(ltrim($command), $prefix) === 0) {
                $isValidCommand = true;
                break;
            }
        }
        
        if (!$isValidCommand) {
            return response()->json([
                'success' => false,
                'output' => 'Command not allowed',
                'error' => 'This command is not permitted for security reasons'
            ]);
        }

        try {
            $process = Process::fromShellCommandline($command);
            $process->setTimeout(30);
            $process->run();
            
            if ($process->isSuccessful()) {
                return response()->json([
                    'success' => true,
                    'output' => $process->getOutput(),
                    'error' => null
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'output' => $process->getOutput(),
                    'error' => $process->getErrorOutput()
                ]);
            }
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'output' => '',
                'error' => $e->getMessage()
            ]);
        }
    }
}