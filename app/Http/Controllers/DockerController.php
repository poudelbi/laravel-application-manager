<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;

class DockerController extends Controller
{
    protected $applicationsDir = '/var/www/html/applications';
    protected $masterAppDir = '/var/www/html/master-app';

    public function index()
    {
        // Get Docker status and running containers
        $dockerStatus = $this->checkDockerStatus();
        $containers = $this->getContainers();
        $images = $this->getImages();
        
        // Get managed applications
        $applications = $this->getApplications();
        
        return view('docker.index', compact('dockerStatus', 'containers', 'images', 'applications'));
    }

    public function getApplications()
    {
        $apps = [];
        $applicationsDir = $this->applicationsDir;
        
        if (file_exists($applicationsDir)) {
            $directories = scandir($applicationsDir);
            foreach ($directories as $dir) {
                if ($dir != '.' && $dir != '..' && is_dir($applicationsDir . '/' . $dir)) {
                    $apps[] = [
                        'name' => $dir,
                        'path' => $applicationsDir . '/' . $dir,
                        'created_at' => filemtime($applicationsDir . '/' . $dir)
                    ];
                }
            }
        }
        return $apps;
    }

    public function checkDockerStatus()
    {
        try {
            $process = new Process(['docker', 'info']);
            $process->setTimeout(10);
            $process->run();
            
            if ($process->isSuccessful()) {
                return [
                    'installed' => true,
                    'running' => true,
                    'version' => $this->getDockerVersion(),
                    'message' => 'Docker is running'
                ];
            } else {
                return [
                    'installed' => true,
                    'running' => false,
                    'version' => null,
                    'message' => 'Docker is installed but not running: ' . $process->getErrorOutput()
                ];
            }
        } catch (\Exception $e) {
            return [
                'installed' => false,
                'running' => false,
                'version' => null,
                'message' => 'Docker is not installed or not accessible: ' . $e->getMessage()
            ];
        }
    }

    public function getDockerVersion()
    {
        try {
            $process = new Process(['docker', '--version']);
            $process->setTimeout(10);
            $process->run();
            
            if ($process->isSuccessful()) {
                return trim(str_replace('Docker version ', '', $process->getOutput()));
            }
        } catch (\Exception $e) {
            return null;
        }
        
        return null;
    }

    public function getContainers()
    {
        try {
            $process = new Process(['docker', 'ps', '--format', '{{.ID}}\t{{.Names}}\t{{.Image}}\t{{.Status}}\t{{.Ports}}']);
            $process->setTimeout(15);
            $process->run();
            
            if ($process->isSuccessful()) {
                $output = $process->getOutput();
                $containers = [];
                
                if (!empty($output)) {
                    $lines = explode("\n", trim($output));
                    foreach ($lines as $line) {
                        if (!empty(trim($line))) {
                            $parts = preg_split('/\t/', $line);
                            if (count($parts) >= 4) {
                                $containers[] = [
                                    'id' => $parts[0],
                                    'name' => $parts[1],
                                    'image' => $parts[2],
                                    'status' => $parts[3],
                                    'ports' => $parts[4] ?? ''
                                ];
                            }
                        }
                    }
                }
                
                return $containers;
            }
        } catch (\Exception $e) {
            \Log::error('Error getting Docker containers: ' . $e->getMessage());
        }
        
        return [];
    }

    public function getImages()
    {
        try {
            $process = new Process(['docker', 'images', '--format', '{{.Repository}}\t{{.Tag}}\t{{.ID}}\t{{.CreatedAt}}\t{{.Size}}']);
            $process->setTimeout(15);
            $process->run();
            
            if ($process->isSuccessful()) {
                $output = $process->getOutput();
                $images = [];
                
                if (!empty($output)) {
                    $lines = explode("\n", trim($output));
                    foreach ($lines as $line) {
                        if (!empty(trim($line))) {
                            $parts = preg_split('/\t/', $line);
                            if (count($parts) >= 3) {
                                $images[] = [
                                    'repository' => $parts[0],
                                    'tag' => $parts[1],
                                    'id' => $parts[2],
                                    'created' => $parts[3] ?? '',
                                    'size' => $parts[4] ?? ''
                                ];
                            }
                        }
                    }
                }
                
                return $images;
            }
        } catch (\Exception $e) {
            \Log::error('Error getting Docker images: ' . $e->getMessage());
        }
        
        return [];
    }

    public function buildAppImage(Request $request, $appName)
    {
        $request->validate([
            'force_rebuild' => 'boolean'
        ]);

        $appPath = $this->applicationsDir . '/' . $appName;
        
        if (!File::exists($appPath)) {
            return redirect()->back()->with('error', 'Application not found: ' . $appName);
        }

        // Check if Dockerfile exists in the app directory, if not create one
        $dockerfilePath = $appPath . '/Dockerfile';
        if (!File::exists($dockerfilePath)) {
            $this->createDockerfileForApp($appPath);
        }

        $imageName = 'laravel-' . $appName . ':latest';
        
        try {
            $cmd = ['docker', 'build', '-t', $imageName, '.'];
            if ($request->boolean('force_rebuild')) {
                $cmd[] = '--no-cache';
            }
            
            $process = new Process($cmd, $appPath);
            $process->setTimeout(300); // 5 minute timeout for build
            $process->run();
            
            if ($process->isSuccessful()) {
                return redirect()->back()->with('success', "Docker image for $appName built successfully: $imageName");
            } else {
                $errorOutput = $process->getErrorOutput();
                \Log::error("Docker build failed for $appName: " . $errorOutput);
                return redirect()->back()->with('error', "Failed to build Docker image for $appName: " . $errorOutput);
            }
        } catch (\Exception $e) {
            \Log::error('Exception during Docker build: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Exception during Docker build: ' . $e->getMessage());
        }
    }

    public function createDockerfileForApp($appPath)
    {
        $dockerfileContent = <<< 'EOT'
FROM ubuntu:22.04

LABEL maintainer="Laravel Application"

# Prevent interactive prompts during package installation
ENV DEBIAN_FRONTEND=noninteractive

# Install system dependencies
RUN apt-get update && apt-get install -y \
    curl \
    zip \
    unzip \
    git \
    supervisor \
    nginx \
    sudo \
    gnupg \
    lsb-release \
    libicu-dev \
    libzip-dev \
    libonig-dev \
    libxml2-dev \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    locales \
    && rm -rf /var/lib/apt/lists/*

# Install PHP 8.2 with required extensions
RUN apt-get update && apt-get install -y \
    php8.2 \
    php8.2-cli \
    php8.2-common \
    php8.2-curl \
    php8.2-mbstring \
    php8.2-xml \
    php8.2-zip \
    php8.2-bcmath \
    php8.2-gd \
    php8.2-pgsql \
    php8.2-sqlite3 \
    php8.2-redis \
    php8.2-dev \
    && rm -rf /var/lib/apt/lists/*

# Configure PHP
RUN echo "upload_max_filesize = 100M" >> /etc/php/8.2/cli/php.ini && \
    echo "post_max_size = 100M" >> /etc/php/8.2/cli/php.ini && \
    echo "memory_limit = 512M" >> /etc/php/8.2/cli/php.ini

RUN echo "upload_max_filesize = 100M" >> /etc/php/8.2/fpm/php.ini && \
    echo "post_max_size = 100M" >> /etc/php/8.2/fpm/php.ini && \
    echo "memory_limit = 512M" >> /etc/php/8.2/fpm/php.ini

# Enable PHP-FPM
RUN sed -i 's/listen = .*/listen = 0.0.0.0:9000/' /etc/php/8.2/fpm/pool.d/www.conf

# Install Composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Install Node.js and npm
RUN curl -fsSL https://deb.nodesource.com/setup_18.x | bash - && \
    apt-get install -y nodejs && \
    rm -rf /var/lib/apt/lists/*

# Set working directory
WORKDIR /var/www/html

# Copy application files
COPY . /var/www/html

# Set proper permissions
RUN chown -R www-data:www-data /var/www/html && \
    chmod -R 755 /var/www/html && \
    chmod -R 775 /var/www/html/storage && \
    chmod -R 775 /var/www/html/bootstrap/cache

# Configure sudo for www-data user (needed for application management)
RUN echo "www-data ALL=(ALL) NOPASSWD: ALL" >> /etc/sudoers

# Create supervisord configuration
RUN mkdir -p /var/log/supervisor
RUN echo -e "[supervisord]\n\
nodaemon=true\n\
logfile=/var/log/supervisor/supervisord.log\n\
logfile_maxbytes=10MB\n\
logfile_backups=10\n\
loglevel=info\n\
pidfile=/var/run/supervisord.pid\n\
childlogdir=/var/log/supervisor\n\
\n\
[program:php-fpm]\n\
command=/usr/sbin/php-fpm8.2 -F\n\
priority=1\n\
autostart=true\n\
autorestart=true\n\
stdout_logfile=/var/log/supervisor/php-fpm.log\n\
stderr_logfile=/var/log/supervisor/php-fpm_error.log" > /etc/supervisor/conf.d/supervisord.conf

# Expose port
EXPOSE 9000

CMD ["/usr/bin/supervisord", "-c", "/etc/supervisor/conf.d/supervisord.conf"]
EOT;

        File::put($appPath . '/Dockerfile', $dockerfileContent);
    }

    public function startAppContainer(Request $request, $appName)
    {
        $port = $request->input('port', 9000);
        $appPath = $this->applicationsDir . '/' . $appName;
        
        if (!File::exists($appPath)) {
            return redirect()->back()->with('error', 'Application not found: ' . $appName);
        }

        $imageName = 'laravel-' . $appName . ':latest';
        $containerName = 'laravel-' . $appName . '-' . time();
        
        // Check if image exists, if not try to build it
        $imageExists = $this->imageExists($imageName);
        if (!$imageExists) {
            // Try to build the image first
            $this->createDockerfileForApp($appPath);
            $buildProcess = new Process(['docker', 'build', '-t', $imageName, '.'], $appPath);
            $buildProcess->setTimeout(300);
            $buildProcess->run();
            
            if (!$buildProcess->isSuccessful()) {
                return redirect()->back()->with('error', "Could not build Docker image for $appName: " . $buildProcess->getErrorOutput());
            }
        }

        try {
            // Stop and remove any existing container with the same name pattern
            $this->stopAndRemoveContainerByNamePattern($containerName);
            
            $process = new Process([
                'docker', 'run', '-d', 
                '--name', $containerName,
                '-p', $port . ':9000',
                '-v', $appPath . ':/var/www/html',
                '-v', $this->applicationsDir . ':/var/www/html/applications',
                '-e', 'DB_CONNECTION=sqlite',
                '-e', 'DB_DATABASE=/var/www/html/database/database.sqlite',
                $imageName
            ]);
            $process->setTimeout(60);
            $process->run();
            
            if ($process->isSuccessful()) {
                return redirect()->back()->with('success', "Docker container for $appName started successfully on port $port. Container: $containerName");
            } else {
                $errorOutput = $process->getErrorOutput();
                \Log::error("Docker run failed for $appName: " . $errorOutput);
                return redirect()->back()->with('error', "Failed to start Docker container for $appName: " . $errorOutput);
            }
        } catch (\Exception $e) {
            \Log::error('Exception during Docker run: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Exception during Docker run: ' . $e->getMessage());
        }
    }

    public function stopAppContainer($containerId)
    {
        try {
            $process = new Process(['docker', 'stop', $containerId]);
            $process->setTimeout(30);
            $process->run();
            
            if ($process->isSuccessful()) {
                // Remove the container after stopping
                $removeProcess = new Process(['docker', 'rm', $containerId]);
                $removeProcess->setTimeout(30);
                $removeProcess->run();
                
                return redirect()->back()->with('success', "Docker container $containerId stopped and removed successfully");
            } else {
                $errorOutput = $process->getErrorOutput();
                return redirect()->back()->with('error', "Failed to stop Docker container $containerId: " . $errorOutput);
            }
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Exception during Docker stop: ' . $e->getMessage());
        }
    }

    public function imageExists($imageName)
    {
        try {
            $process = new Process(['docker', 'images', '-q', $imageName]);
            $process->setTimeout(15);
            $process->run();
            
            if ($process->isSuccessful()) {
                return !empty(trim($process->getOutput()));
            }
        } catch (\Exception $e) {
            \Log::error('Error checking if image exists: ' . $e->getMessage());
        }
        
        return false;
    }

    public function stopAndRemoveContainerByNamePattern($containerName)
    {
        try {
            // List containers with the name pattern
            $process = new Process(['docker', 'ps', '-aq', '-f', 'name=' . $containerName]);
            $process->setTimeout(15);
            $process->run();
            
            if ($process->isSuccessful()) {
                $containerIds = trim($process->getOutput());
                if (!empty($containerIds)) {
                    $ids = explode("\n", $containerIds);
                    foreach ($ids as $id) {
                        if (!empty(trim($id))) {
                            // Stop the container
                            $stopProcess = new Process(['docker', 'stop', trim($id)]);
                            $stopProcess->setTimeout(30);
                            $stopProcess->run();
                            
                            // Remove the container
                            $rmProcess = new Process(['docker', 'rm', trim($id)]);
                            $rmProcess->setTimeout(30);
                            $rmProcess->run();
                        }
                    }
                }
            }
        } catch (\Exception $e) {
            \Log::error('Error stopping/removing containers: ' . $e->getMessage());
        }
    }

    public function getAppDockerStatus($appName)
    {
        $containers = $this->getContainers();
        $appContainers = [];
        
        foreach ($containers as $container) {
            if (strpos($container['name'], 'laravel-' . $appName) !== false) {
                $appContainers[] = $container;
            }
        }
        
        return response()->json([
            'appName' => $appName,
            'containers' => $appContainers,
            'hasContainers' => count($appContainers) > 0
        ]);
    }
}