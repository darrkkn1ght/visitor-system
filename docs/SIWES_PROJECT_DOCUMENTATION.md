# Visitor Management System — Project Documentation

## 1. Project Overview
The **Visitor Management System (VMS)** is a comprehensive, secure, and real-time web-based platform designed to streamline the process of managing visitors within an institution. The system replaces traditional paper-based logs with a digital, auditable, and efficient workflow for tracking visitor arrivals, departures, and facility access.

*   **Purpose**: To enhance institutional security, improve visitor experience, and provide administrators with real-time visibility and a forensic audit trail of all visitor-related activities.
*   **Organization**: Built for the **University of Ibadan (UI)**.
*   **Technology Stack**:
    *   **Backend**: PHP 8.2 (Core logic and API)
    *   **Database**: MySQL/MariaDB (Relational data storage)
    *   **Frontend**: Vanilla HTML5, CSS3 (Modern Glassmorphism design), and JavaScript (ES6+)
    *   **Realtime**: Node.js with WebSockets (ws/Socket.io) for instant notifications
    *   **Environment**: XAMPP (Apache, MySQL, PHP) on Windows
    *   **Client-Side**: Service Workers for Desktop Notifications

## 2. System Architecture
The system follows a modular architecture that separates public-facing visitor forms, administrative dashboards, and real-time communication services.

*   **Frontend**: Responsive web interface styled with official University of Ibadan branding (Indigo-Blue: `#1E3A8A`, Gold: `#D4AF37`).
*   **Backend**: PHP-based server-side logic handles authentication, form processing, and database interactions using prepared statements for security.
*   **Database**: A relational MariaDB database stores visitors, users, destinations, keycards, events, and audit logs.
*   **Realtime Server**: A dedicated Node.js server running in the background manages persistent WebSocket tunnels, allowing the system to push updates to admins instantly without page refreshes.

### Folder Structure Overview
*   `assets/`: Shared CSS and JS libraries.
*   `db/`: Database connection configuration (`db.php`) and schema check scripts.
*   `docs/`: Comprehensive technical documentation, audit reports, and setup guides.
*   `image/`: Project logos, background images, and security audit screenshots.
*   `includes/`: Reusable PHP components (e.g., `logo_helper.php`, `realtime_notify.php`).
*   `migrations/`: Version-controlled SQL scripts for database schema updates and policy enforcement.
*   `realtime/`: Node.js server source code (`server.js`) and dependencies.

## 3. Key Features
*   **Visitor Check-in**: A streamlined form for visitors to enter their details, select a destination, and receive an automatically assigned physical keycard.
*   **Visitor Check-out**: A secure process to record departure times and release assigned keycards back into the available pool.
*   **Admin Dashboard**: A real-time monitoring interface for staff to see active visitors, filter records by destination, and manage unread notifications.
*   **Audit Logging**: An immutable tracking system that records every administrative action (logins, exports, data changes) with IP addresses and timestamps.
*   **Events Calendar**: A centralized scheduling tool for tracking facility availability and planned institutional events.
*   **Backup & Restore**: Functionality for exporting system records into CSV format for offline storage and reporting, protected by rate-limiting.
*   **Profile Management**: Granular control over admin availability status (Available, Busy, Away, Unavailable) which updates in real-time across the system.

## 4. Database Design
The system utilizes a structured relational schema to ensure data integrity and performance. Key tables include:

*   `visitors`: Stores visitor details, assigned keycards, unique tokens, and check-in/out timestamps.
*   `users`: Manages administrative accounts with specific roles and destination assignments.
*   `destinations`: Defines facilities/departments within the organization.
*   `keycards`: Tracks physical access cards and their assignment status.
*   `audit_log`: The central repository for forensic audit data.
*   `notifications`: Stores real-time alerts for administrators.
*   `events`: Manages the institutional calendar.
*   `login_attempts`: Implements brute-force protection by tracking failed login attempts.
*   `rate_limit_log`: Monitors resource-intensive actions like data exports.

*Reference: All schema modifications are tracked in the `migrations/` folder (e.g., `001_create_login_attempts_table.sql`, `003_create_audit_log_table.sql`).*

## 5. My Contributions During SIWES
During my industrial training, I focused on enhancing the system's security, user experience, and real-time capabilities.

### Frontend
*   **Visitor Form Enhancement**: Redesigned the check-in form with professional styling and implemented robust client-side validation using JavaScript.
*   **Mobile Navigation**: Developed a premium, responsive mobile navigation drawer for administrators.
*   **Visual Consistency**: Updated the entire application to use the official University of Ibadan color palette and typography.

### Backend
*   **Receptionist Dashboard Fix**: Modified `admin_dashboard.php` to allow receptionists to view historical records, enabling them to check out visitors who arrived on previous days.
*   **Form Validation Logic**: Implemented strict server-side validation in `submit.php` to ensure data integrity and security.

### Testing & DevOps
*   **Realtime Notifications**: Implemented browser desktop notifications using Service Workers and WebSockets, ensuring admins get notified even when the dashboard tab is hidden.
*   **Documentation**: Authored the `COMPLETE_TESTING_CHECKLIST.md` and `VISUAL_TESTING_REFERENCE.md` to guide future testing cycles.

## 6. Implementation Highlights
*   **Visitor Check-in Flow**: Uses a dual-validation approach (Client + Server) with automatic keycard assignment from a destination-specific pool and cryptographically secure token generation.
*   **Admin Authentication**: Protects accounts using bcrypt password hashing (cost factor 12) and an automated lockout mechanism (5 failed attempts in 15 minutes).
*   **Audit Logging System**: Captures detailed metadata for every action, including old/new values in JSON format, IP address, and browser user-agent.
*   **Realtime Server**: Built with Node.js and WebSockets, the server utilizes a fail-safe restart loop via `run_realtime_server.bat` ensuring high availability.
*   **Automation**: Configured the realtime server to run on system boot via Windows Task Scheduler and Startup folder shortcuts.
*   **XAMPP Configuration**: Optimized Apache and MySQL service configurations to start automatically as Windows Services for maximum reliability.
*   **Backup & Restore**: Implemented rate-limited CSV exports to prevent data scraping while ensuring institutional data can be backed up securely.

## 7. Testing
System reliability was verified through exhaustive testing cycles documented in the following project files:
*   [COMPLETE_TESTING_CHECKLIST.md](../COMPLETE_TESTING_CHECKLIST.md): Covers form validation, receptionist roles, and checkout consistency.
*   [VISUAL_TESTING_REFERENCE.md](../VISUAL_TESTING_REFERENCE.md): Details the verification of real-time WebSocket communication and browser notification prompts.

## 8. Screenshots
- Visitor Check-in Form: ![Visitor Check-in Form](../image/screenshot_checkin.png)
- Admin Dashboard: ![Admin Dashboard](../image/screenshot_dashboard.png)
- Audit Logs Viewer: ![Audit Logs](../image/screenshot_audit.png)
- Calendar View: ![Calendar](../image/screenshot_calendar.png)
- Backup & Restore: ![Backup](../image/screenshot_backup.png)
- Realtime Server Running: ![Realtime Server](../image/screenshot_realtime.png)
- XAMPP Services Active: ![XAMPP](../image/screenshot_xampp.png)

## 9. Challenges and Solutions
*   **Real-time Desynchronization**: Initially, admin dashboards required manual refreshes to show new visitors. This was resolved by implementing a WebSocket-based real-time server that pushes updates instantly.
*   **Security Hardening**: The system faced challenges with Cross-Site Scripting (XSS). This was solved by implementing a strict Content Security Policy (CSP) and ensuring all user output is escaped using `htmlspecialchars`.
*   **Access Control**: Developing a system that balanced receptionist usability with administrative security was achieved by refining Role-Based Access Control (RBAC) logic in `rbac.php`.

## 10. Conclusion
Working on the University of Ibadan Visitor Management System has been an invaluable experience during my SIWES placement. I have gained deep practical knowledge in full-stack web development, specifically in:
*   Real-time system architecture with Node.js and WebSockets.
*   Advanced database management and versioning using SQL migrations.
*   Enterprise-grade security practices including CSP, CSRF protection, and audit trailing.
*   Responsive UI/UX design following institutional branding guidelines.

These skills are foundational to my growth as a software engineer and have prepared me for future professional challenges in the tech industry.
