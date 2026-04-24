# LogicLens

A comprehensive programming assessment platform built for educational environments. LogicLens enables teachers to create and manage programming activities while providing students with an interactive learning experience through role-based access control.

![Laravel](https://img.shields.io/badge/Laravel-12.x-FF2D20?style=flat&logo=laravel)
![Vue.js](https://img.shields.io/badge/Vue.js-3.x-4FC08D?style=flat&logo=vue.js)
![Inertia.js](https://img.shields.io/badge/Inertia.js-2.x-9553E9?style=flat)
![TypeScript](https://img.shields.io/badge/TypeScript-5.x-3178C6?style=flat&logo=typescript)
![Tailwind CSS](https://img.shields.io/badge/Tailwind_CSS-4.x-06B6D4?style=flat&logo=tailwind-css)
![Docker](https://img.shields.io/badge/Docker-Ready-2496ED?style=flat&logo=docker)

## 📸 Screenshots

![LogicLens Dashboard](.github/images/image1.png)
![LogicLens Dashboard](.github/images/image2.png)
![LogicLens Dashboard](.github/images/image3.png)
![LogicLens Dashboard](.github/images/image4.png)
![LogicLens Dashboard](.github/images/image5.png)
![LogicLens Dashboard](.github/images/image6.png)

## 🚀 Quick Start

```bash
# Clone and setup main application
git clone https://github.com/GabTamayo/LogicLens.git
cd LogicLens
cp .env.example .env

# Install dependencies
# Option A: If you have Composer installed locally
composer install

# Option B: If you don't have Composer (using Docker)
docker run --rm -u "$(id -u):$(id -g)" -v "$(pwd):/var/www/html" -w /var/www/html laravelsail/php83-composer:latest composer install --ignore-platform-reqs

# Start services
./vendor/bin/sail up -d
./vendor/bin/sail artisan key:generate
./vendor/bin/sail artisan migrate
./vendor/bin/sail npm install && ./vendor/bin/sail npm run dev

# Start workers (in separate terminals)
./vendor/bin/sail artisan queue:work
./vendor/bin/sail artisan schedule:work

# Setup Detector Service
cd ..
git clone https://github.com/GabTamayo/LogicLens-Detector-Service.git
cd LogicLens-Detector-Service
docker-compose up -d --build

# Setup Piston API
cd ..
`Checkout: https://github.com/engineer-man/piston`

curl -X POST http://localhost:2000/api/v2/packages -H "Content-Type: application/json" -d '{"language": "java", "version": "15.0.2"}'
curl -X POST http://localhost:2000/api/v2/packages -H "Content-Type: application/json" -d '{"language": "python", "version": "3.12.0"}'
```

**Access the application**: http://localhost
**Mailpit (Email Testing)**: http://localhost:8025
**Detector Service**: http://localhost:8001
**Piston API**: http://localhost:2000

## 🌐 Service Endpoints

| Service              | URL                     | Purpose                          |
| -------------------- | ----------------------- | -------------------------------- |
| **Main Application** | http://localhost        | LogicLens web interface          |
| **Mailpit UI**       | http://localhost:8025   | View test emails                 |
| **Detector Service** | http://localhost:8001   | Code analysis API                |
| **Piston API**       | http://localhost:2000   | Code execution engine            |
| **Supabase**         | db.xxx.supabase.co:5432 | PostgreSQL Database (cloud)      |
| **Redis**            | localhost:6379          | Cache & Queue (use `sail redis`) |

## 📋 Table of Contents

- [Quick Start](#-quick-start)
- [Features](#-features)
- [System Architecture](#️-system-architecture)
- [Tech Stack](#-tech-stack)
- [Prerequisites](#-prerequisites)
- [Installation](#-installation)
- [Configuration](#️-configuration)
- [Environment Variables Reference](#-environment-variables-reference)
- [Usage](#-usage)
- [Useful Sail Commands](#-useful-sail-commands)
- [Testing](#-testing)
- [Troubleshooting](#-troubleshooting)
- [Project Structure](#-project-structure)
- [Contributing](#-contributing)
- [Known Issues](#-known-issues)
- [Roadmap](#-roadmap)
- [License](#-license)

## ✨ Features

### Role-Based Access Control

- **Student Role**: Register, enroll in classes, and complete programming activities
- **Teacher Role**: Create classes, design activities, and manage assessments

### Teacher Capabilities

- Create and manage multiple classes
- Design programming activities with custom requirements
- Assign activities to specific classes
- Track student progress and submissions
- View analytics and performance metrics

### Student Capabilities

- Browse and enroll in available classes
- Access assigned programming activities
- Submit code solutions
- Track personal progress and grades
- View feedback from teachers

### Core Functionality

- **Activity Management**: Create, edit, and organize programming assignments
- **Class Management**: Organize students into classes for better course structure
- **Assignment System**: Link activities to classes for targeted learning
- **Queue-Based Processing**: Asynchronous handling of code submissions and evaluations
- **Real-time Dashboard**: Monitor active links, pending evaluations, and statistics
- **Code Execution**: Safe code execution via Piston API
- **Plagiarism Detection**: Code similarity analysis via Detector Service
- **AI Assistance**: Optional AI-powered code review and feedback

## 🏗️ System Architecture

LogicLens consists of three main services:

### 1. Main Application (Laravel + Inertia.js)

- **Port**: 80 (http://localhost)
- **Purpose**: Web application, user management, activity creation
- **Services**: Redis (cache/queue), Mailpit (email testing)
- **Database**: Supabase PostgreSQL (cloud-hosted)

### 2. Detector Service (Python/FastAPI)

- **Port**: 8001 (http://localhost:8001)
- **Repository**: [LogicLens-Detector-Service](https://github.com/GabTamayo/LogicLens-Detector-Service)
- **Purpose**: Code plagiarism detection and similarity analysis
- **Technology**: Python, FastAPI, Docker

### 3. Piston API (Code Execution Engine)

- **Port**: 2000 (http://localhost:2000)
- **Purpose**: Safe, sandboxed code execution for testing submissions
- **Supported Languages**: Java 15.0.2, Python 3.12.0 (configurable)

```
┌─────────────────────────────────────────────────────────────┐
│                       LogicLens System                      │
├─────────────────────────────────────────────────────────────┤
│                                                             │
│  ┌──────────────┐      ┌──────────────┐                     │
│  │   Laravel    │◄────►│   Detector   │                     │
│  │  Application │      │   Service    │                     │
│  │   (Port 80)  │      │  (Port 8001) │                     │
│  └───┬───┬──┬───┘      └──────────────┘                     │
│      │   │  │                                               │
│      │   │  └──────────────────┐                            │
│      │   │                     │                            │
│  ┌───▼───▼───────┐       ┌────▼───────┐                     │
│  │ Redis │Mailpit│       │   Piston   │                     │
│  │(Cache)│(Email)│       │    API     │                     │
│  │(Queue)│       │       │(Port 2000) │                     │
│  └───┬───────────┘       └────────────┘                     │
│      │                                                      │
│      │  ┌──────────────────────────────────────┐            │
│      └─►│      Supabase PostgreSQL             │            │
│         │      (Cloud Database)                │            │
│         └──────────────────────────────────────┘            │
└─────────────────────────────────────────────────────────────┘
```

## 🛠 Tech Stack

### Backend

- **Laravel 12.x** - Modern PHP framework
- **PHP ^8.2** - Latest PHP features and performance

### Frontend

- **Vue.js 3.x** - Progressive JavaScript framework
- **Inertia.js v2** - SPA framework for Laravel
- **TypeScript** - Type-safe JavaScript
- **Tailwind CSS v4** - Utility-first CSS framework

### Build Tools

- **Vite v7** - Next generation frontend tooling
- **Laravel Vite Plugin** - Laravel integration
- **Wayfinder** - Enhanced routing

### Infrastructure & Services

- **Docker & Docker Compose** - Containerization
- **Laravel Sail** - Docker development environment
- **Supabase** - Cloud PostgreSQL database
- **Redis** - In-memory data store for caching and queues
- **Mailpit** - Email testing tool

### Code Analysis & Execution

- **Piston API** - Sandboxed code execution engine
- **Detector Service** - Python-based code similarity detection

### Testing

- **Pest v4** - Testing framework
- **PHPUnit 12** - Unit testing

## 📦 Prerequisites

Before you begin, ensure you have the following:

- Docker Desktop (for Laravel Sail)
- Docker Compose
- Git
- **Supabase Account** - [Sign up for free](https://supabase.com)
- **Optional**: AI API Key (for enhanced features)

## 🚀 Installation

LogicLens uses **Laravel Sail** for Docker-based development, which includes all necessary services (PostgreSQL, Redis, Mailpit) pre-configured.

### 1. Clone the Repository

```bash
git clone https://github.com/GabTamayo/LogicLens.git
cd LogicLens
```

### 2. Environment Setup

```bash
cp .env.example .env
```

### 3. Install Dependencies via Sail

If you don't have Composer installed locally, you can use Docker to install dependencies:

```bash
docker run --rm \
    -u "$(id -u):$(id -g)" \
    -v "$(pwd):/var/www/html" \
    -w /var/www/html \
    laravelsail/php83-composer:latest \
    composer install --ignore-platform-reqs
```

### 4. Start Sail Services

```bash
./vendor/bin/sail up -d
```

This will start:

- **Redis** (Cache & Queue)
- **Mailpit** (Email testing)

### 5. Configure Supabase Database

1. Go to [Supabase Dashboard](https://app.supabase.com)
2. Create a new project or use existing one
3. Navigate to **Settings** → **Database**
4. Copy your connection details

Update your `.env` file with Supabase credentials:

```env
DB_CONNECTION=pgsql
DB_HOST=db.xxxxxxxxxxxxxx.supabase.co
DB_PORT=5432
DB_DATABASE=postgres
DB_USERNAME=postgres
DB_PASSWORD=your_supabase_password
```

> **Note**: You can find these details in your Supabase project settings under "Database" → "Connection string" → "URI"

### 6. Generate Application Key

```bash
./vendor/bin/sail artisan key:generate
```

### 7. Run Migrations

```bash
./vendor/bin/sail artisan migrate
```

### 8. Seed Database (Optional)

```bash
./vendor/bin/sail artisan db:seed
```

### 9. Install Node Dependencies and Build Assets

```bash
./vendor/bin/sail npm install
./vendor/bin/sail npm run dev
```

### 10. Start Queue Workers and Scheduler

Open **two separate terminal windows**:

**Terminal 1 - Queue Worker:**

```bash
./vendor/bin/sail artisan queue:work
```

**Terminal 2 - Scheduler:**

```bash
./vendor/bin/sail artisan schedule:work
```

The application will be available at `http://localhost`

### 11. Setup Detector Service

The LogicLens Detector Service handles code analysis and plagiarism detection.

```bash
# Clone the detector service repository
git clone https://github.com/GabTamayo/LogicLens-Detector-Service.git
cd LogicLens-Detector-Service

# Build and run with Docker Compose
docker-compose up -d --build
```

The detector service will be available at `http://localhost:8001`

### 12. Setup Piston API (Code Execution Engine)

Piston is used for safe code execution and testing. First, start the Piston container:

```bash
docker run -d \
  --name piston \
  -p 2000:2000 \
  ghcr.io/engineer-man/piston
```

Then install the required language runtimes:

```bash
# Install Java 15.0.2
curl -X POST http://localhost:2000/api/v2/packages \
  -H "Content-Type: application/json" \
  -d '{"language": "java", "version": "15.0.2"}'

# Install Python 3.12.0
curl -X POST http://localhost:2000/api/v2/packages \
  -H "Content-Type: application/json" \
  -d '{"language": "python", "version": "3.12.0"}'
```

Verify installation:

```bash
curl http://localhost:2000/api/v2/runtimes
```

### 13. Configure AI API (Optional)

LogicLens supports AI-powered features via Prism. Add your AI API key to `.env`:

```env
# AI Configuration (Optional)
PRISM_API_KEY=your_ai_api_key_here
PRISM_MODEL=gpt-4
```

> **Note**: The Prism.php configuration is already set up. Simply add your API key to enable AI features.

## ⚙️ Configuration

### Services Overview

LogicLens uses the following services:

**Local (via Laravel Sail):**

- **Redis** - Cache and queue backend (Port: 6379)
- **Mailpit** - Email testing interface (Port: 8025 for UI, 1025 for SMTP)

**External:**

- **Supabase PostgreSQL** - Cloud-hosted database
- **Detector Service** - Code analysis (Port: 8001)
- **Piston API** - Code execution (Port: 2000)

### Queue Configuration

LogicLens uses Redis for queue management. The configuration is already set in `.env`:

```env
QUEUE_CONNECTION=redis
```

Queue workers are started with:

```bash
./vendor/bin/sail artisan queue:work
```

### Scheduler Configuration

The scheduler handles periodic tasks. Run it with:

```bash
./vendor/bin/sail artisan schedule:work
```

### Email Testing with Mailpit

Mailpit provides a local email testing interface. Access it at:

```
http://localhost:8025
```

All emails sent by the application will appear here during development.

### Detector Service Configuration

Add the detector service URL to your `.env`:

```env
DETECTOR_SERVICE_URL=http://host.docker.internal:8001
```

> **Note**: Use `host.docker.internal` when running LogicLens in Docker to communicate with services on the host machine.

### Piston API Configuration

Configure the Piston code execution engine in `.env`:

```env
PISTON_API_URL=http://host.docker.internal:2000
```

### AI Configuration (Optional)

For AI-powered features, configure Prism in `.env`:

```env
# AI API Configuration
PRISM_API_KEY=your_api_key_here
PRISM_MODEL=gpt-4
PRISM_BASE_URL=https://api.openai.com/v1
```

### Production Considerations

For production deployment:

1. **Queue Management**: Use Laravel Horizon or Supervisor

    ```bash
    ./vendor/bin/sail artisan horizon
    ```

2. **Supabase Production**:
    - Use production Supabase project
    - Enable connection pooling (PgBouncer)
    - Configure SSL mode: `sslmode=require` in database URL

3. **Email Service**: Replace Mailpit with a production SMTP service (SendGrid, Mailgun, etc.)

4. **Redis Persistence**: Configure Redis for data persistence in production

5. **HTTPS**: Use a reverse proxy (Nginx/Caddy) with SSL certificates

6. **Environment**: Set `APP_ENV=production` and `APP_DEBUG=false`

## 📖 Usage

### For Teachers

1. **Register as a Teacher**
    - Navigate to the registration page
    - Select "Teacher" role during registration
    - Verify your email address

2. **Create a Class**
    - Access the "Classes" dashboard
    - Click "Create New Class"
    - Fill in class details (name, description, schedule)
    - Save the class

3. **Create Activities**
    - Navigate to "Activities" section
    - Click "Create Activity"
    - Define activity requirements, test cases, and constraints
    - Save the activity

4. **Assign Activities to Classes**
    - Open the desired activity
    - Select target classes for assignment
    - Set deadlines and parameters
    - Publish the assignment

5. **Monitor Progress**
    - View student submissions
    - Check completion rates
    - Provide feedback

### For Students

1. **Register as a Student**
    - Navigate to the registration page
    - Select "Student" role during registration
    - Verify your email address

2. **Enroll in Classes**
    - Browse available classes
    - Click "Enroll" on desired classes
    - Wait for teacher approval (if required)

3. **Access Activities**
    - View assigned activities in your dashboard
    - Click on an activity to see requirements

4. **Submit Solutions**
    - Write your code solution
    - Test locally before submission
    - Submit through the platform
    - Track submission status

5. **View Results**
    - Check grades and feedback
    - Review test case results
    - Improve and resubmit if allowed

## 📁 Project Structure

```
LogicLens/
├── app/
│   ├── Http/
│   │   ├── Controllers/     # Application controllers
│   │   └── Middleware/      # Custom middleware
│   ├── Models/              # Eloquent models
│   ├── Services/            # Business logic services
│   └── Jobs/                # Queue jobs
├── database/
│   ├── migrations/          # Database migrations
│   └── seeders/             # Database seeders
├── resources/
│   ├── js/
│   │   ├── Components/      # Vue components
│   │   ├── Pages/           # Inertia pages
│   │   └── Layouts/         # Layout components
│   └── css/                 # Stylesheets
├── routes/
│   ├── web.php              # Web routes
│   └── api.php              # API routes
├── tests/                   # Application tests
├── public/                  # Public assets
└── storage/                 # Application storage
```

## 🧪 Testing

Run the test suite:

```bash
# Run all tests
./vendor/bin/sail artisan test

# Run specific test file
./vendor/bin/sail artisan test --filter=ActivityTest

# Run tests with coverage
./vendor/bin/sail artisan test --coverage
```

Using Pest:

```bash
./vendor/bin/sail pest

# With coverage
./vendor/bin/sail pest --coverage
```

## 🚢 Useful Sail Commands

```bash
# Start services in the background
./vendor/bin/sail up -d

# Stop services
./vendor/bin/sail down

# View logs
./vendor/bin/sail logs

# Access Laravel container shell
./vendor/bin/sail shell

# Run Artisan commands
./vendor/bin/sail artisan migrate
./vendor/bin/sail artisan make:controller ExampleController

# Run Composer commands
./vendor/bin/sail composer require package/name
./vendor/bin/sail composer update

# Run NPM commands
./vendor/bin/sail npm install
./vendor/bin/sail npm run dev
./vendor/bin/sail npm run build

# Access database
# For Supabase, use their dashboard or connect via psql:
# psql "postgresql://postgres:password@db.xxx.supabase.co:5432/postgres"

# Access Redis CLI
./vendor/bin/sail redis

# Run tests
./vendor/bin/sail test
./vendor/bin/sail pest

# Clear caches
./vendor/bin/sail artisan cache:clear
./vendor/bin/sail artisan config:clear
./vendor/bin/sail artisan route:clear
./vendor/bin/sail artisan view:clear

# Queue management
./vendor/bin/sail artisan queue:work
./vendor/bin/sail artisan queue:restart
./vendor/bin/sail artisan queue:failed

# Create alias for easier use (add to ~/.bashrc or ~/.zshrc)
alias sail='[ -f sail ] && sh sail || sh vendor/bin/sail'
# Then use: sail up -d, sail artisan migrate, etc.
```

## 🤝 Contributing

Contributions are welcome! Please follow these steps:

1. Fork the repository
2. Create a feature branch (`git checkout -b feature/AmazingFeature`)
3. Commit your changes (`git commit -m 'Add some AmazingFeature'`)
4. Push to the branch (`git push origin feature/AmazingFeature`)
5. Open a Pull Request

### Coding Standards

- Follow PSR-12 coding standards for PHP
- Use ESLint and Prettier for JavaScript/TypeScript
- Write meaningful commit messages
- Add tests for new features
- Update documentation as needed

## 📝 Development Guidelines

### PHP/Laravel

- Follow Laravel best practices
- Use Eloquent ORM for database operations
- Implement service classes for complex business logic
- Use form requests for validation
- Write feature and unit tests

### Vue.js/TypeScript

- Use Composition API
- Follow TypeScript best practices
- Create reusable components
- Implement proper prop validation
- Use TypeScript interfaces for type safety

## 📋 Environment Variables Reference

Here's a complete reference of important environment variables:

```env
# Application
APP_NAME=LogicLens
APP_ENV=local
APP_KEY=                    # Generated by 'sail artisan key:generate'
APP_DEBUG=true
APP_URL=http://localhost

# Database (Supabase PostgreSQL)
DB_CONNECTION=pgsql
DB_HOST=db.xxxxxxxxxxxxxx.supabase.co
DB_PORT=5432
DB_DATABASE=postgres
DB_USERNAME=postgres
DB_PASSWORD=your_supabase_password

# Redis (Sail auto-configures these)
REDIS_HOST=redis
REDIS_PASSWORD=null
REDIS_PORT=6379

# Queue
QUEUE_CONNECTION=redis

# Mail (Mailpit - Sail auto-configures)
MAIL_MAILER=smtp
MAIL_HOST=mailpit
MAIL_PORT=1025
MAIL_USERNAME=null
MAIL_PASSWORD=null
MAIL_ENCRYPTION=null

# External Services
DETECTOR_SERVICE_URL=http://host.docker.internal:8001
PISTON_API_URL=http://host.docker.internal:2000

# AI Configuration (Optional)
PRISM_API_KEY=your_api_key_here
PRISM_MODEL=gpt-4
PRISM_BASE_URL=https://api.openai.com/v1
```

## 🔧 Troubleshooting

### Common Issues

#### Sail Commands Not Working

```bash
# Make sure you're in the project directory
cd LogicLens

# Ensure Docker is running
docker ps

# Restart Sail services
./vendor/bin/sail down
./vendor/bin/sail up -d
```

#### Permission Issues

```bash
# Fix storage permissions
./vendor/bin/sail artisan storage:link
sudo chown -R $USER:$USER storage bootstrap/cache
```

#### Database Connection Issues

```bash
# Recreate database
./vendor/bin/sail artisan migrate:fresh

# Check database connection
./vendor/bin/sail artisan tinker
>>> DB::connection()->getPdo();
```

#### Queue Not Processing

```bash
# Restart queue worker
./vendor/bin/sail artisan queue:restart
./vendor/bin/sail artisan queue:work

# Check failed jobs
./vendor/bin/sail artisan queue:failed
```

#### Piston API Not Responding

```bash
# Check if Piston is running
docker ps | grep piston

# Restart Piston
docker restart piston

# Check logs
docker logs piston
```

#### Detector Service Connection Issues

```bash
# Check detector service status
cd LogicLens-Detector-Service
docker-compose ps

# Restart detector service
docker-compose restart

# View logs
docker-compose logs -f
```

### Stopping All Services

```bash
# Stop main application
cd LogicLens
./vendor/bin/sail down

# Stop detector service
cd LogicLens-Detector-Service
docker-compose down

# Stop Piston
docker stop piston
```

### Resetting Everything

```bash
# WARNING: This will delete all data!
./vendor/bin/sail down -v
./vendor/bin/sail artisan migrate:fresh --seed
```

## 🐛 Known Issues

- Docker must be running before starting Sail
- Detector service must be running before submitting code for analysis
- Piston API requires language runtime installation before first use
- AI features require valid API key configuration

## 🗺 Roadmap

- [x] Docker-based development environment with Laravel Sail
- [x] Role-based access control (Teacher/Student)
- [x] Queue-based job processing
- [x] Code execution via Piston API
- [x] Plagiarism detection service
- [ ] Real-time code execution and testing feedback
- [ ] Enhanced plagiarism detection algorithms
- [ ] Support for additional programming languages (C++, JavaScript, Go)
- [ ] Mobile application (iOS/Android)
- [ ] Advanced analytics dashboard with visualizations
- [ ] Peer code review features
- [ ] Collaborative coding activities
- [ ] Video tutorials integration
- [ ] Automated grading with AI assistance
- [ ] Integration with LMS platforms (Moodle, Canvas)

## 📄 License

This project is open-sourced software licensed under the [MIT license](LICENSE).

## 👥 Authors

- **Gabriel Tamayo** - _Initial work_ - [@GabTamayo](https://github.com/GabTamayo)

## 🙏 Acknowledgments

- Laravel community for excellent documentation
- Vue.js team for the reactive framework
- Inertia.js for seamless SPA integration
- All contributors and testers

## 📞 Support

For support, please open an issue in the GitHub repository or contact the maintainers.

---

Made with ❤️ for educational programming assessment
