<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>{{ config('app.name', 'Laravel') }}</title>

    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    
    <style>
        body {
            background-color: #f8f9fa;
        }
        .navbar-brand {
            font-weight: bold;
        }
        .card {
            box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
            border: 1px solid rgba(0, 0, 0, 0.125);
            transition: box-shadow 0.15s ease-in-out;
        }
        .card:hover {
            box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
        }
        .feature-icon {
            width: 4rem;
            height: 4rem;
            border-radius: 0.75rem;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 1rem;
        }
        .bg-primary-gradient {
            background: linear-gradient(135deg, #0d6efd, #0b5ed7);
        }
        .bg-success-gradient {
            background: linear-gradient(135deg, #198754, #157347);
        }
        .bg-info-gradient {
            background: linear-gradient(135deg, #0dcaf0, #31d2f2);
        }
        .bg-warning-gradient {
            background: linear-gradient(135deg, #ffc107, #ffca2c);
        }
    </style>
</head>
<body>
    <!-- Navigation Bar -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand" href="{{ url('/') }}">
                <i class="fas fa-cube me-2"></i>{{ config('app.name', 'Laravel') }}
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link active" href="{{ url('/') }}"><i class="fas fa-home me-1"></i> Home</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('applications.index') }}"><i class="fas fa-th-large me-1"></i> Applications</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('sites.index') }}"><i class="fas fa-globe me-1"></i> Sites</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('test-dashboard.index') }}"><i class="fas fa-chart-line me-1"></i> Test Dashboard</a>
                    </li>
                </ul>
                <ul class="navbar-nav">
                    @auth
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="fas fa-user me-1"></i> {{ Auth::user()->name }}
                            </a>
                            <ul class="dropdown-menu" aria-labelledby="navbarDropdown">
                                <li><a class="dropdown-item" href="{{ route('profile.edit') }}">Profile</a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li>
                                    <form method="POST" action="{{ route('logout') }}">
                                        @csrf
                                        <button type="submit" class="dropdown-item">Logout</button>
                                    </form>
                                </li>
                            </ul>
                        </li>
                    @else
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('login') }}">Login</a>
                        </li>
                    @endauth
                </ul>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="py-5 bg-primary-gradient text-white">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-6">
                    <h1 class="display-4 fw-bold">Laravel Application Manager</h1>
                    <p class="lead">Deploy, manage, and monitor multiple Laravel applications with ease</p>
                    <div class="mt-4">
                        <a href="{{ route('applications.index') }}" class="btn btn-light btn-lg me-2">
                            <i class="fas fa-plus-circle me-1"></i> Deploy New App
                        </a>
                        <a href="{{ route('test-dashboard.index') }}" class="btn btn-outline-light btn-lg">
                            <i class="fas fa-cogs me-1"></i> Run Tests
                        </a>
                    </div>
                </div>
                <div class="col-lg-6 text-center">
                    <i class="fas fa-server fa-7x opacity-75"></i>
                </div>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section class="py-5">
        <div class="container">
            <div class="row mb-5">
                <div class="col-12 text-center">
                    <h2 class="fw-bold">Powerful Features</h2>
                    <p class="text-muted">Everything you need to manage Laravel applications efficiently</p>
                </div>
            </div>
            
            <div class="row g-4">
                <div class="col-md-6 col-lg-3">
                    <div class="card h-100">
                        <div class="card-body text-center">
                            <div class="feature-icon bg-primary-gradient text-white mx-auto mb-3">
                                <i class="fas fa-code fa-2x"></i>
                            </div>
                            <h5 class="card-title">Git Integration</h5>
                            <p class="card-text">Clone repositories with custom names and configurations</p>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-6 col-lg-3">
                    <div class="card h-100">
                        <div class="card-body text-center">
                            <div class="feature-icon bg-success-gradient text-white mx-auto mb-3">
                                <i class="fas fa-magic fa-2x"></i>
                            </div>
                            <h5 class="card-title">Auto Configuration</h5>
                            <p class="card-text">Automatic nginx config generation and port assignment</p>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-6 col-lg-3">
                    <div class="card h-100">
                        <div class="card-body text-center">
                            <div class="feature-icon bg-info-gradient text-white mx-auto mb-3">
                                <i class="fas fa-plug fa-2x"></i>
                            </div>
                            <h5 class="card-title">Dependency Management</h5>
                            <p class="card-text">Composer and NPM integration for all dependencies</p>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-6 col-lg-3">
                    <div class="card h-100">
                        <div class="card-body text-center">
                            <div class="feature-icon bg-warning-gradient text-dark mx-auto mb-3">
                                <i class="fas fa-shield-alt fa-2x"></i>
                            </div>
                            <h5 class="card-title">Security</h5>
                            <p class="card-text">Proper permissions and authentication management</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Pages and Categories Section -->
    <section class="py-5 bg-light">
        <div class="container">
            <div class="row mb-5">
                <div class="col-12 text-center">
                    <h2 class="fw-bold">Content Management</h2>
                    <p class="text-muted">Manage pages and categories with our integrated CMS</p>
                </div>
            </div>
            
            <div class="row">
                <div class="col-md-6">
                    <div class="card mb-4">
                        <div class="card-header bg-primary text-white">
                            <h5 class="mb-0"><i class="fas fa-folder me-2"></i>Page Categories</h5>
                        </div>
                        <div class="card-body">
                            <p class="card-text">Organize your content with customizable categories</p>
                            <ul class="list-group list-group-flush">
                                @forelse($categories as $category)
                                    <li class="list-group-item d-flex justify-content-between align-items-center">
                                        {{ $category->name }}
                                        <span class="badge bg-primary rounded-pill">{{ $category->pages->count() }}</span>
                                    </li>
                                @empty
                                    <li class="list-group-item text-center text-muted">
                                        <i class="fas fa-inbox fa-2x mb-2"></i>
                                        <p>No categories created yet</p>
                                    </li>
                                @endforelse
                            </ul>
                            <div class="mt-3">
                                <a href="{{ route('page-categories.index') }}" class="btn btn-primary btn-sm">
                                    <i class="fas fa-list me-1"></i> View All Categories
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-6">
                    <div class="card mb-4">
                        <div class="card-header bg-success text-white">
                            <h5 class="mb-0"><i class="fas fa-file-alt me-2"></i>Recent Pages</h5>
                        </div>
                        <div class="card-body">
                            <p class="card-text">Manage your content pages efficiently</p>
                            <div class="list-group">
                                @forelse($recentPages as $page)
                                    <a href="{{ route('pages.show', $page->id) ?? '#' }}" class="list-group-item list-group-item-action">
                                        <div class="d-flex w-100 justify-content-between">
                                            <h6 class="mb-1">{{ Str::limit($page->title, 30) }}</h6>
                                            <small>{{ $page->created_at->diffForHumans() }}</small>
                                        </div>
                                        <p class="mb-1">{{ Str::limit(strip_tags($page->content), 60) }}</p>
                                        <small class="text-muted">
                                            @if($page->category)
                                                <span class="badge bg-secondary">{{ $page->category->name }}</span>
                                            @endif
                                        </small>
                                    </a>
                                @empty
                                    <div class="text-center text-muted p-4">
                                        <i class="fas fa-file fa-2x mb-2"></i>
                                        <p>No pages created yet</p>
                                    </div>
                                @endforelse
                            </div>
                            <div class="mt-3">
                                <a href="{{ route('pages.index') }}" class="btn btn-success btn-sm">
                                    <i class="fas fa-plus me-1"></i> Create New Page
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Quick Actions Section -->
    <section class="py-5">
        <div class="container">
            <div class="row mb-5">
                <div class="col-12 text-center">
                    <h2 class="fw-bold">Quick Actions</h2>
                    <p class="text-muted">Perform common tasks with a single click</p>
                </div>
            </div>
            
            <div class="row g-4">
                <div class="col-md-4">
                    <div class="card text-center">
                        <div class="card-body">
                            <div class="feature-icon bg-primary-gradient text-white mx-auto mb-3">
                                <i class="fas fa-download fa-2x"></i>
                            </div>
                            <h5 class="card-title">Deploy Laravel</h5>
                            <p class="card-text">Deploy a new official Laravel application with one click</p>
                            <a href="{{ route('applications.create') }}" class="btn btn-primary">
                                <i class="fas fa-rocket me-1"></i> Deploy Now
                            </a>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-4">
                    <div class="card text-center">
                        <div class="card-body">
                            <div class="feature-icon bg-success-gradient text-white mx-auto mb-3">
                                <i class="fas fa-terminal fa-2x"></i>
                            </div>
                            <h5 class="card-title">Run Artisan</h5>
                            <p class="card-text">Execute Laravel Artisan commands directly</p>
                            <a href="{{ route('test-dashboard.index') }}" class="btn btn-success">
                                <i class="fas fa-cogs me-1"></i> Artisan Console
                            </a>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-4">
                    <div class="card text-center">
                        <div class="card-body">
                            <div class="feature-icon bg-info-gradient text-white mx-auto mb-3">
                                <i class="fas fa-paint-brush fa-2x"></i>
                            </div>
                            <h5 class="card-title">Manage Content</h5>
                            <p class="card-text">Create and manage pages and categories</p>
                            <a href="{{ route('pages.index') ?? '#' }}" class="btn btn-info">
                                <i class="fas fa-edit me-1"></i> Content Manager
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="bg-dark text-light py-4">
        <div class="container">
            <div class="row">
                <div class="col-md-6">
                    <p class="mb-0">© {{ date('Y') }} Laravel Application Manager by <a href="https://nextmorse.com/" class="text-light">NextMorse</a></p>
                </div>
                <div class="col-md-6 text-md-end">
                    <p class="mb-0">Powered by Laravel v{{ app()->version() }}</p>
                </div>
            </div>
        </div>
    </footer>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>