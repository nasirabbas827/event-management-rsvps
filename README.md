# Event-Management-RSVPs

A lightweight PHP web application that lets event organizers create and manage events, while attendees can browse event details, RSVP, and view their reservations. The system includes separate dashboards for organizers and attendees, secure login/logout flows, and a clean responsive UI.

---

## Overview

The **Event-Management-RSVPs** project provides a simple yet functional platform for managing events and RSVP submissions. Organizers can add, edit, and delete events, upload images, and monitor attendee responses. Attendees can browse upcoming events, view detailed information, and submit or cancel RSVPs.

---

## Features

- **Organizer Dashboard**
  - Create, edit, and delete events.
  - Upload event images.
  - View a list of all RSVPs per event.
- **Attendee Dashboard**
  - Browse upcoming events.
  - View detailed event information.
  - Submit or cancel RSVPs.
  - View personal RSVP history.
- **Authentication**
  - Secure login / logout for both organizers and attendees.
- **Responsive UI**
  - Clean navigation bars and styled components (`css/style.css`).
- **Database**
  - MySQL schema (`Database/event_db.sql`) with tables for users, events, and RSVPs.

---

## Tech Stack

| Layer | Technology |
|-------|------------|
| Backend | PHP 7.x+ |
| Database | MySQL |
| Front‑end | HTML5, CSS3 (custom stylesheet), minimal JavaScript |
| Server | Apache / Nginx (LAMP stack) |
| Version Control | Git (GitHub) |

---

## Installation

1. **Clone the repository**

   ```bash
   git clone https://github.com/yourusername/Event-Management-RSVPs.git
   cd Event-Management-RSVPs
   ```

2. **Set up the database**

   - Create a new MySQL database (e.g., `event_db`).
   - Import the schema:

     ```bash
     mysql -u your_user -p event_db < Database/event_db.sql
     ```

3. **Configure the application**

   - Copy `config.php.example` (if provided) to `config.php` and update the credentials:

     ```php
     // config.php
     define('DB_HOST', 'localhost');
     define('DB_NAME', 'event_db');
     define('DB_USER', 'your_user');
     define('DB_PASS', 'your_password');
     ```

   - Do the same for the `attendee/config.php` and `organizer/config.php` files if they exist.

4. **Set file permissions**

   ```bash
   chmod -R 755 organizer/uploads
   ```

5. **Start the server**

   - If using the built‑in PHP server (for development):

     ```bash
     php -S localhost:8000
     ```

   - Or place the project in your Apache/Nginx document root.

6. **Access the app**

   - Organizer login: `http://localhost:8000/login.php` (use credentials seeded in the DB).
   - Attendee login/registration: `http://localhost:8000/register.php`.

---

## Usage

### Organizer Workflow

1. **Log in** via `login.php`.
2. Navigate to **Organizer Dashboard** (`organizer/organizer_dashboard.php`).
3. Use **Add Event** (`organizer/add_event.php`) to create a new event.
4. Edit or delete events via `organizer/edit_event.php`.
5. View RSVPs for a specific event with `organizer/view_rsvps.php`.

### Attendee Workflow

1. **Register** or **log in** via `register.php` / `login.php`.
2. Browse events on the home page (`index.php`) or via the **Attendee Dashboard** (`attendee/attendee_dashboard.php`).
3. Click an event to see details (`attendee/event_details.php