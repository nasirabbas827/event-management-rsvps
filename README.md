# event-management-rsvps  

A simple web‑based RSVP system that lets **organizers** create and manage events, and **attendees** view event details and submit their responses. Built with PHP and a MySQL database, the application demonstrates clean separation of concerns (organizer vs. attendee) and includes a responsive UI.

---

## Overview  

- **Organizers** can add, edit, and delete events, upload images, and view RSVP lists.  
- **Attendees** can browse upcoming events, view details, and submit their RSVP status (Going / Not Going).  
- Secure login/logout flows for both user types.  
- Centralised configuration files (`config.php`) for easy environment setup.  

---

## Features  

| ✅ | Feature |
|---|---------|
| ✔️ | Organizer dashboard with event CRUD operations |
| ✔️ | Attendee dashboard to browse events and submit RSVPs |
| ✔️ | Separate authentication for organizers and attendees |
| ✔️ | Image upload handling for event flyers |
| ✔️ | Responsive navigation bars (`navbar.php`) for both roles |
| ✔️ | Centralised CSS (`css/style.css`) for a clean UI |
| ✔️ | SQL script (`Database/event_db.sql`) to initialise the schema |
| ✔️ | Comprehensive documentation (`Project File.docx`) |

---

## Tech Stack  

| Component | Technology |
|-----------|------------|
| Backend   | PHP 8.x |
| Database  | MySQL / MariaDB |
| Frontend  | HTML5, CSS3 (custom stylesheet), minimal JavaScript |
| Server    | Apache / Nginx (any LAMP stack) |
| Version Control | Git (GitHub) |

---

## Installation  

### 1. Prerequisites  

- PHP 8.0+ with PDO extension  
- MySQL server  
- Web server (Apache/Nginx) configured to serve PHP files  
- Composer (optional, only if you add third‑party packages later)  

### 2. Clone the repository  

```bash
git clone https://github.com/yourusername/event-management-rsvps.git
cd event-management-rsvps
```

### 3. Set up the database  

```bash
# From the project root
mysql -u root -p < Database/event_db.sql
```

> **Note:** The SQL script creates a database named `event_management` with the required tables (`organizers`, `attendees`, `events`, `rsvps`). Adjust the credentials in the next step if you use a different user/database name.

### 4. Configure connection settings  

Copy the sample config and update the placeholders:

```bash
cp config.php.example config.php
```

Edit `config.php` (and the duplicate files in `attendee/` and `organizer/` if you prefer per‑module configs) to match your environment:

```php
<?php
define('DB_HOST', 'localhost');
define('DB_NAME', 'event_management');
define('DB_USER', 'YOUR_DB_USERNAME');
define('DB_PASS', 'YOUR_DB_PASSWORD');
?>
```

### 5. Set file permissions  

If you plan to upload event images, ensure the `organizer/uploads/` directory is writable:

```bash
chmod -R 755 organizer/uploads/
```

### 6. (Optional) Virtual host configuration  

For a clean URL structure, you may add a virtual host pointing the document root to the project folder. Example for Apache:

```apacheconf
<VirtualHost *:80>
    ServerName event-management.local
    DocumentRoot /path/to/event-management-rsvps

    <Directory /path/to/event-management-rsvps>
        AllowOverride All
        Require all granted
    </Directory>
</VirtualHost>
```

Remember to update your hosts file (`127.0.0.1 event-management.local`).

---

## Usage  

### 1. Access the application  

- **Attendee portal:** `http://