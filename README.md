## Project Detector

Project Detector is a Laravel 12 + Inertia.js (Vue 3) application for managing programming activities and analyzing code submissions. It provides activity links for students or participants, queues detection jobs in the background, and exposes a dashboard with metrics such as active links, pending detections, and average scores.

### Features

- **Activity & Submission Management**: Create activities, generate shareable activity links, and receive code submissions.
- **Detection Pipeline**: Queue-based detection jobs (via `DetectionJob` and `DetectionService`) to analyze submissions asynchronously.
- **Dashboard & Analytics**: Authenticated dashboard summarizing active links, pending detections, and scoring statistics.
- **Modern SPA Stack**: Inertia.js with Vue 3, TypeScript, and Tailwind CSS 4 for a smooth single-page experience.
- **Authentication**: Laravel authentication with email verification.

---

## Tech Stack

- **Backend**: Laravel **12.x** (PHP **^8.2**)
- **Frontend**: Vue **3.x**, Inertia.js **v2**, TypeScript
- **Styling**: Tailwind CSS **v4**
- **Build Tooling**: Vite **v7**, Laravel Vite Plugin, Wayfinder
- **Database**: PostgreSQL (default in this project)
- **Testing**: Pest **v4**, PHPUnit **12**

---

## License

This project is open-sourced software licensed under the **MIT license** (see the `LICENSE` file if present, or the default Laravel license terms).
