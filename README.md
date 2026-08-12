# 🏛️ Barangay Inquirer System

### Digital Barangay Services, Inquiry, and Document Request Management Platform

A modern **web-based Barangay Inquirer System** designed to digitalize and simplify common barangay services. The system allows residents to submit document requests and inquiries online, monitor request progress, receive notifications, and access barangay announcements.

For barangay administrators and staff, the system provides centralized request management, resident records, document processing, reporting, activity monitoring, and role-based access control.

---

## 📌 Project Overview

The **Barangay Inquirer System** is designed to reduce reliance on manual logbooks and paper-based processes by providing a centralized digital platform for managing resident inquiries, document requests, records, and barangay information.

The system provides separate dashboards and permissions for:

* 👤 **Resident** — Submit and monitor requests
* 🧑‍💼 **Admin / Barangay Staff** — Process and manage resident requests
* 👑 **Super Admin** — Manage administrators and oversee the entire system

The goal is to make barangay services more **organized, accessible, transparent, and efficient**.

---

## 🎯 Objectives

The system aims to:

* Digitize common barangay services and inquiries
* Reduce manual paperwork and logbook dependency
* Allow residents to submit requests online
* Provide real-time request status tracking
* Notify residents about important request updates
* Centralize resident and request records
* Improve administrative workflow
* Provide secure role-based system access
* Generate organized reports and activity records

---

## ✨ Core Features

### 👤 Resident Portal

Residents can access a dedicated dashboard where they can:

* 📊 View dashboard statistics
* 📄 Request barangay documents
* 📋 View submitted requests
* 🔎 Track request status
* 📑 View request details and history
* 🔔 Receive notifications
* 📢 View barangay announcements
* 👤 Manage their profile
* 🔐 Change their password
* 🚪 Securely log out

### 📄 Document Request Management

Residents can submit requests for documents such as:

* Barangay Clearance
* Purok Clearance
* Certificate of Indigency
* Cedula
* Residency Certificate
* Business Permit Clearance
* Other available barangay documents

Each request can follow a structured workflow:

```text
Submitted
    ↓
Pending
    ↓
Processing
    ↓
Approved / Ready for Pickup
    ↓
Completed
```

Requests may also be rejected when necessary, with the reason recorded for the resident.

---

## 🔔 Notification System

Residents are informed about important changes to their requests.

Example workflow:

```text
Resident submits request
        ↓
Request recorded as Pending
        ↓
Resident receives notification
        ↓
Admin reviews request
        ↓
Processing
        ↓
Resident receives status update
        ↓
Approved / Rejected
        ↓
Resident receives final notification
```

Notifications may include:

* Request successfully submitted
* Request is being processed
* Request approved
* Request ready for pickup
* Request rejected
* Rejection reason
* Other important barangay announcements

---

# 🧑‍💼 Admin Dashboard

The Admin Dashboard provides barangay personnel with tools to manage daily operations.

### Dashboard Overview

Administrators can view:

* Total residents
* Total requests
* Pending requests
* Processing requests
* Approved requests
* Rejected requests
* Recent activities

### Request Management

Admins can:

* View resident requests
* Search and filter requests
* Review request information
* Update request status
* Approve requests
* Reject requests with a reason
* Mark documents as ready for pickup
* View request history

### Resident Management

Admins can:

* View resident records
* Search residents
* View resident information
* Monitor resident requests
* Manage resident-related records

### Announcements

Admins can:

* Create announcements
* Edit announcements
* Publish announcements
* Remove announcements
* Share important barangay updates

### Reports

Admins can:

* View request statistics
* Search records
* Filter transactions
* Print reports
* Export records when implemented

---

# 👑 Super Admin Dashboard

The Super Admin has the highest level of system access.

### Super Admin Features

* 📊 Dashboard overview
* 👥 Manage administrators
* ➕ Add admin accounts
* ✏️ Update admin accounts
* 🔐 Reset admin passwords
* 🚫 Disable or manage admin accounts
* 📋 View all requests
* 📊 Monitor system activities
* 🔎 Review audit logs
* 🚪 Secure logout

The Super Admin is responsible for controlling administrative access and overseeing the overall operation of the system.

---

# 🔐 Role-Based Access Control

The system separates access according to user roles.

| Role           | Main Responsibilities                                      |
| -------------- | ---------------------------------------------------------- |
| 👤 Resident    | Submit requests, track requests, manage profile            |
| 🧑‍💼 Admin    | Process requests, manage residents, announcements, reports |
| 👑 Super Admin | Manage admins and oversee the entire system                |

### Access Flow

```text
                    LOGIN
                      │
                      ▼
               Authentication
                      │
          ┌───────────┼───────────┐
          ▼           ▼           ▼
       Resident      Admin    Super Admin
          │           │           │
          ▼           ▼           ▼
     Resident      Admin       Super Admin
     Dashboard    Dashboard     Dashboard
```

Unauthorized users cannot directly access protected dashboards.

---

# 🛡️ Security Features

The system follows a **layered security approach** to protect user accounts and system data.

### Authentication

* Secure login
* Password hashing
* Session regeneration
* Secure logout
* Role-based authentication

### Application Security

* Laravel CSRF protection
* Input validation
* Eloquent ORM / parameter binding
* Protection against common SQL injection techniques
* Escaped output to reduce XSS risks
* Protected routes and middleware

### Access Control

* Resident-only routes
* Admin-only routes
* Super Admin-only routes
* Principle of least privilege

### Monitoring

The system can maintain activity records for important actions such as:

* Login attempts
* Administrative actions
* Request status changes
* Account changes
* Password-related actions

### Backup

Recommended deployment practices include:

* Regular database backups
* Off-site backup storage
* Backup before major system updates
* Periodic restore testing

> **Note:** Security is continuously improved as the system moves from development toward production deployment.

---

# 🗄️ Database

The system uses **MySQL** as its relational database.

Laravel migrations are used to manage the database structure.

Example database flow:

```text
Laravel Application
        │
        ▼
    Eloquent ORM
        │
        ▼
      MySQL
        │
        ├── Users
        ├── Requests
        ├── Announcements
        ├── Sessions
        ├── Notifications
        └── Activity Logs
```

The database stores:

* User accounts
* Resident information
* Document requests
* Request statuses
* Announcements
* Notifications
* Administrative records
* Activity information

---

# 🛠️ Technology Stack

### Backend

* **PHP 8+**
* **Laravel Framework**

### Frontend

* HTML5
* CSS3
* JavaScript
* Bootstrap
* Bootstrap Icons

### Database

* MySQL
* Laravel Eloquent ORM
* Laravel Migrations

### Development Tools

* XAMPP
* Visual Studio Code
* Git
* GitHub
* Composer
* Node.js / npm

---

# 🏗️ System Architecture

The system follows the **MVC (Model-View-Controller)** architecture provided by Laravel.

```text
                 USER
                  │
                  ▼
             WEB BROWSER
                  │
                  ▼
              ROUTES
                  │
                  ▼
             CONTROLLERS
                  │
          ┌───────┴───────┐
          ▼               ▼
       MODELS           VIEWS
          │               │
          ▼               ▼
       MySQL          Blade / UI
```

This structure separates application logic, database operations, and user interface components.

---

# 📂 Main System Modules

```text
Barangay Inquirer System
│
├── Authentication
│   ├── Login
│   ├── Registration
│   ├── Logout
│   └── Password Management
│
├── Resident Portal
│   ├── Dashboard
│   ├── Request Documents
│   ├── My Requests
│   ├── Request Details
│   ├── Notifications
│   ├── Announcements
│   └── Profile Management
│
├── Admin Portal
│   ├── Dashboard
│   ├── Request Management
│   ├── Resident Management
│   ├── Announcements
│   ├── Reports
│   └── Activity Monitoring
│
└── Super Admin Portal
    ├── Dashboard
    ├── Admin Management
    ├── Request Monitoring
    ├── Audit Logs
    └── System Oversight
```

---

# 🔄 Request Processing Workflow

```text
Resident
   │
   │ Submit Request
   ▼
System
   │
   │ Create Request
   ▼
Pending
   │
   │ Admin Reviews
   ▼
Processing
   │
   ├───────────────┐
   ▼               ▼
Approved         Rejected
   │               │
   ▼               ▼
Ready for        Reason
Pickup           Provided
   │               │
   └───────┬───────┘
           ▼
       Notification
           │
           ▼
        Resident
```

---

# 📱 Responsive Design

The system is designed to work across different screen sizes:

* 💻 Desktop
* 💻 Laptop
* 📱 Mobile
* 📟 Tablet

The interface uses responsive Bootstrap components and custom CSS to provide a consistent user experience.

---

# 📊 Benefits

### For Residents

* Convenient online requests
* Reduced waiting and manual processing
* Request tracking
* Status notifications
* Easier access to barangay information

### For Barangay Staff

* Centralized request management
* Faster processing
* Organized resident records
* Easier monitoring
* Reduced paperwork
* Better reporting

### For Barangay Administration

* Better visibility of transactions
* Centralized records
* Improved accountability
* Activity monitoring
* More organized digital operations

---

# 🚀 Installation & Setup

## 1. Clone the Repository

```bash
git clone https://github.com/your-username/barangay-inquirer-system.git
cd barangay-inquirer-system
```

## 2. Install PHP Dependencies

```bash
composer install
```

## 3. Install Frontend Dependencies

```bash
npm install
```

## 4. Create Environment File

```bash
cp .env.example .env
```

For Windows, you can also manually copy `.env.example` and rename it to:

```text
.env
```

## 5. Generate Application Key

```bash
php artisan key:generate
```

## 6. Configure MySQL

Create a MySQL database through XAMPP/phpMyAdmin and update `.env`:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=inquiry_system
DB_USERNAME=root
DB_PASSWORD=
```

## 7. Run Migrations

```bash
php artisan migrate
```

If you are working with a fresh development database:

```bash
php artisan migrate:fresh
```

> ⚠️ `migrate:fresh` deletes existing tables and recreates them. Do not use it on a production database unless you intentionally want to destroy the existing data.

## 8. Start the Development Server

```bash
php artisan serve
```

Then open:

```text
http://127.0.0.1:8000
```

---

# 🧪 Development Status

| Module                   | Status                     |
| ------------------------ | -------------------------- |
| 🏠 Homepage              | 🟡 In Development          |
| 🔐 Authentication        | 🟢 Implemented             |
| 👤 Resident Dashboard    | 🟢 Implemented / Enhancing |
| 📄 Document Requests     | 🟢 Implemented / Enhancing |
| 🔔 Notifications         | 🟡 In Development          |
| 📢 Announcements         | 🟢 Implemented / Enhancing |
| 🧑‍💼 Admin Dashboard    | 🟢 Implemented / Enhancing |
| 👑 Super Admin Dashboard | 🟢 Implemented / Enhancing |
| 🛡️ Security             | 🟡 Being Enhanced          |
| 📊 Reports               | 🟡 In Development          |
| 🎨 UI/UX                 | 🟡 Being Enhanced          |
| 🚀 Production Deployment | 🔴 Not Yet Deployed        |

---

# 🔮 Future Enhancements

Potential future improvements include:

* Two-Factor Authentication (2FA)
* Email verification
* SMS notifications
* QR-based document verification
* Online appointment scheduling
* Advanced analytics
* Automated report generation
* Cloud-based deployment
* Web Application Firewall
* Enhanced audit logging
* Automated backup and recovery
* Progressive Web App (PWA) support

---

# 🎓 Project Purpose

This project was developed as an academic and practical web application project to demonstrate the use of modern web development technologies in solving real-world barangay service and information management challenges.

It focuses on improving the digital management of:

**Residents → Requests → Processing → Notifications → Records**

---

# 👨‍💻 Developer

**Genecyl Colipano**

**Full-Stack Web Developer | AI-Driven Creator | Automation Enthusiast**

### Technologies & Interests

`Laravel` `PHP` `MySQL` `JavaScript` `Bootstrap` `Git` `GitHub` `AI` `Automation`

---

# 📄 License

This project is intended primarily for **educational and academic purposes**.

© 2026 Barangay Inquirer System. All Rights Reserved.
