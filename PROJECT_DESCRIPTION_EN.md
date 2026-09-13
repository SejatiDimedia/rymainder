# Rymainder — Automated Recurring Donor Pledge & Retention Platform

**Rymainder** (bearing the platform identity *Pledge Cloud*) is a web-based automated recurring pledge management and donor retention platform specifically engineered to help charitable foundations, orphanages, non-profit organizations (NGOs), and community programs cultivate donor commitments with professional, timely, and respectful communication.

The system bridges foundation administrators (**Super Admins & Staff Officers**) and **Regular Donors (Sponsors & Program Guardians)** through automated multi-channel touchpoints across **Official Telegram Bots**, **WhatsApp**, and **Email**.

Rymainder acts as an intelligent, automated donor retention assistant: it continuously tracks each donor's commitment cadence (monthly, quarterly, semi-annually, or annually) and delivers friendly, personalized reminders according to pre-configured wave schedules (such as 7 days before, 3 days before, and on the due date itself). Donors can activate Telegram bot reminders with a single tap or a simple 6-digit verification code, without creating passwords or filling cumbersome account forms. On the management side, foundation staff maintain full command over active sponsor pipelines, bulk Excel/CSV donor onboarding, real-time message delivery audit logs, and a resilient, fail-safe resend engine that effortlessly recovers from third-party network hiccups.

This project focuses on guaranteed timely delivery with zero duplicate messaging, seamless bot onboarding, high operational resilience, tolerant bulk spreadsheet imports, and a clean, modern interface designed to foster institutional donor trust.

---

## Project Snapshot

- **Category:** Non-Profit Tech, Donor Management Systems (DMS), Automated Notification & Retention Platforms.
- **Role:** Full-Stack Web & Automation Engineer (Solo Developer).
- **Core Focus:** Automated reminder wave scheduling, two-way interactive Telegram bot integration, multi-channel messaging (Telegram, WhatsApp, Email), tolerant Excel/CSV bulk donor import engine, fail-safe resend & retry mechanism, donor commitment pipeline dashboard, and dynamic variable message personalization.
- **Backend & Architecture:** Laravel 11 (PHP 8.3), Domain-Driven Design (Sponsor, Reminder, Communication, and Admin domains), asynchronous background job queues with exponential backoff retries, and strict Role-Based Access Control (*Super Admin & Staff*).
- **Database & Message Integrity:** MySQL / Relational Database, ACID-compliant transactional schema featuring atomic log locking (preventing double-sending when schedulers overlap), and a complete audit trail (*status: pending, sent, failed, skipped*).
- **Frontend & UI:** Tailwind CSS, Alpine.js, Blade Components, modern Swiss minimalist design language (*Obsidian Charcoal palette, Emerald & Sky Blue accents, sharp typography, and custom accessible modals replacing default browser alerts*).
- **API Integrations & Services:** Telegram Bot API (instant 6-digit onboarding code link and automatic plain-text fallback on markdown parsing issues), International Phone Number Normalization (E.164), SMTP Mail Driver, and PhpSpreadsheet Engine for `.xlsx` and `.csv` processing.

---

## Background & Project Rationale

For charitable foundations, orphan care institutions, and social welfare programs, recurring donors represent the lifeblood of their daily operations. However, in day-to-day operations, the vast majority of non-profits still rely on manual, cumbersome workflows.

Before this platform was introduced, foundation teams routinely encountered severe operational bottlenecks:

1. **Exhausting, Time-Consuming Manual Follow-Ups:** At the start and end of every month, staff had to manually sift through spreadsheets, identify who was due, copy phone numbers, and draft individual WhatsApp messages to hundreds of donors one by one.
2. **Delayed Pledges Stem from Forgetfulness, Not Reluctance:** Donors rarely default because they lost interest; rather, daily life responsibilities simply cause them to overlook their donation dates when not greeted with a gentle, timely prompt.
3. **The Risk of Duplicate Reminders:** When multiple staff members divide follow-up duties manually, communication wires get crossed. Donors occasionally received duplicate reminders from different officers, creating an uncomfortable and unprofessional impression.
4. **Fragmented Communication Preferences:** Some donors prioritize Telegram bots, others prefer WhatsApp messages, and corporate donors check transactional emails. Tracking these disparate preferences manually is notoriously error-prone.
5. **Silent Delivery Failures:** If an email landed in spam or a bot API timed out, administrators had no way of knowing the reminder failed—until the donor inadvertently lapsed.
6. **Disorganized Legacy Donor Data in Spreadsheets:** Organizations often maintained fragmented donor sheets with non-standardized phone numbers (inconsistent spaces, dashes, or missing country codes) and irregular date formats.

Rymainder was engineered to eliminate these challenges completely through dependable automation and thoughtful user experience design:

1. **Hands-Free Scheduled Automation:** The system silently scans active donor commitments every morning at a designated hour, matching due dates and dispatching courteous reminders to the right person at the right time.
2. **Guaranteed Zero Duplicate Delivery:** Built with atomic log status locking, ensuring that repeated scheduler executions or multiple administrator clicks never send duplicate notifications for the same cycle.
3. **Frictionless Telegram Bot Onboarding:** Donors connect their Telegram app via a single direct invite link or by sending a quick 6-digit code to the bot, linking accounts instantly without requiring user registration or passwords.
4. **Intelligent, Tolerant Spreadsheet Importer:** Foundation managers can import hundreds of donor records in seconds. The engine automatically normalizes phone numbers to standard E.164 (+62), reconciles various date formats, decodes Excel numeric date serials, and detects duplicates.
5. **Centralized Delivery Audit & One-Click Resend Engine:** Every notification lifecycle is recorded with complete transparency. If a message encounters a temporary network timeout, administrators can resend single or bulk failed messages with one click through an informative confirmation modal.

---

## Key Contributions

**Automated Multi-Wave Reminder Engine**
- Architected an intelligent recurring reminder scheduler that dynamically computes donor cycle due dates across monthly, quarterly, semi-annual, and annual frequencies.
- Implemented multi-stage reminder waves: informational advance notice (H-7), upcoming reminder (H-3), due-day notification, and gentle overdue follow-ups, with configurable daily dispatch hours aligned with local foundation timezones (WIB, WITA, WIT).

**Interactive Two-Way Telegram Bot Integration & Resilient Fallback**
- Developed an official Telegram bot driver that links donor profiles via unique onboarding links or `/start CODE` verification.
- Engineered a built-in fail-safe mechanism: whenever Telegram rejects a message due to unescaped special characters in Markdown, the driver automatically re-transmits the payload as plain text, ensuring delivery never breaks.
- Built an in-app **Quick Bot Connectivity Test Ping Tool** allowing administrators to test their bot credentials and trigger live messages directly to their personal chat ID from the web browser.

**Resilient, Fail-Safe Delivery & Retry System**
- Created an end-to-end recovery engine capable of re-dispatching standard wave reminders, custom broadcast campaigns, and direct manual reminders through background worker jobs.
- Implemented **"Retry All Failed Reminders"** for bulk one-click queueing, as well as dedicated per-row retry actions in donor profiles and delivery logs, safeguarded by interactive confirmation modals.
- Provided a dedicated CLI automation command (`php artisan reminders:retry-failed`) complete with a dry-run preview mode for automated maintenance scripts.

**High-Capacity Smart Spreadsheet Importer**
- Built an Excel (`.xlsx`, `.xls`) and CSV ingestion engine using PhpSpreadsheet featuring dynamic, case-insensitive column header mapping.
- Handled legacy formatting hurdles: automatically converted Excel numeric date serials (e.g. integer `46280`) into proper date objects, stripped non-numeric currency characters (`Rp 500.000`), and standardized phone numbers into international format (`+62812...`).
- Provided granular duplicate resolution options (skip or update existing records) alongside downloadable starter templates formatted with realistic sample data.

**Standalone Custom Reminders & Broadcast Campaigns**
- Designed an ad-hoc broadcast tool for seasonal campaigns (such as Ramadan food baskets, holiday orphan gifts, or dormitory renovations) targeted at all active donors or selected cohorts.
- Supported flexible one-time or recurring schedules, complete with an immediate manual execution trigger (*Run Now*).

**Modern User Experience & Accessible Custom Modals**
- Replaced rigid native browser alerts (`confirm()`) with custom Alpine.js modal dialogs styled to match the application's clean design system.
- Modals provide complete contextual information prior to submission: recipient name, channel badge, wave schedule, target due date, previous failure callouts, and submit-state loading spinners to prevent double-submissions.
- Refreshed interface iconography across setting pages using crisp, clean Heroicon SVG vectors, replacing non-standard text emoticons.

---

## Core Product Features

**1. Donor & Pledge Pipeline Management**
- Comprehensive donor profiles: name, email, phone number, sponsored beneficiary/orphan, commitment amount, frequency cadence, and preferred communication channels.
- Clear status lifecycle (*Active*, *Paused*, *Cancelled*) ensuring only active donors receive automated notifications.
- Integrated delivery history table per donor profile showing the status and channel of every message sent.

**2. Automated Multichannel Reminder Waves**
- Automated delivery through Telegram Bot, WhatsApp, and Email.
- Customizable message templates for every wave supporting dynamic variable tags: `{sponsor_name}`, `{orphan_name}`, `{amount}`, `{due_date}`, and `{platform_name}`.
- Configurable daily execution time matched to the foundation's official timezone (WIB, WITA, WIT).

**3. Custom Broadcast Campaigns (Ad-Hoc Notifications)**
- Create special announcements or urgent donation campaigns outside standard cycle schedules.
- Flexible recipient filtering: broadcast to all active donors or hand-pick specific sponsors.
- Schedule for precise future execution or dispatch immediately via *Run Now*.

**4. Delivery Audit Logs & Diagnostics Center**
- Centralized audit record for every outgoing message capturing real-time delivery status (*Pending, Sent, Failed, Skipped*), timestamp, channel, and message body.
- Automatic diagnostic callouts documenting provider failure reasons (e.g., SMTP rejection or unlinked Telegram chat ID).
- Single and bulk resend actions with zero-duplicate protection.

**5. Direct Manual Reminders from Donor Profile**
- One-click trigger allowing staff to compose and send personalized, direct reminders right from the donor’s page.
- Live template picker and message preview area that auto-populates donor data and pledge amounts.

**6. Bulk Importer & Starter Template Generator (Excel & CSV)**
- Upload Excel/CSV spreadsheets to onboard hundreds of donors in seconds.
- Informative row-by-row error reporting highlighting invalid rows without corrupting valid data.
- Direct download of styled starter templates in `.xlsx` and `.csv` formats.

**7. Foundation Branding & Telegram Settings**
- Customize organization name, platform tagline, timezone, and official seal/logo.
- Full Telegram bot configuration with real-time status indicators and instant connection testing.
- Template editor for Telegram bot system messages (successful activation, welcome `/start`, default auto-reply, and expired code responses).

---

## Technical Architecture & Tech Stack

Rymainder is built on modern, battle-tested software foundations emphasizing delivery reliability, asynchronous processing, and long-term maintainability:

- **Web Framework & API:** Laravel 11 (PHP 8.3)
- **Application Architecture:** Domain-Driven Design (Sponsor, Reminder, Communication, and Admin domains)
- **Frontend & Interface:** Blade Templates, Tailwind CSS, Alpine.js, Vector Heroicons
- **Database & Persistence:** MySQL / Relational Database with Eloquent ORM & Versioned Migrations
- **Background Processing & Queues:** Laravel Queue Workers & Task Scheduler (`schedule:work`), Exponential Backoff Retries
- **Spreadsheet Processing:** PhpSpreadsheet (`phpoffice/phpspreadsheet` v5.9) supporting `.xlsx`, `.xls`, and `.csv`
- **Integrations:** Telegram Bot API (HTTP Client), SMTP Mail Drivers, E.164 Phone Normalization Helper
- **Security & Authorization:** CSRF Protection, Encrypted Session Cookies, Role-Based Access Control (*Super Admin & Staff*), Database-backed Credential Management
- **Testing & Quality Assurance:** PHPUnit & Pest Test Suite (99 Automated Tests, 373 assertions with 100% feature coverage)

---

## Real Engineering Challenges & Applied Solutions

**1. Eliminating Double-Sending Hazards in Automated Schedulers**
- *Challenge:* When daily cron jobs run on high-frequency schedules, process overlaps or transient server delays can cause race conditions. If unhandled, a donor could accidentally receive duplicate reminder notifications on the same day, eroding trust in the organization.
- *Solution:* I implemented an atomic database transaction pattern that writes a provisional log record in `pending` status before pushing the job to the queue worker. The candidate query checks for existing logs within the current due-date cycle. If a record already exists, the donor is automatically skipped.

**2. Taming Inconsistent Real-World Legacy Excel Data**
- *Challenge:* Non-profit organizations typically inherit years of messy Excel spreadsheets: phone numbers entered with dashes or missing country codes (`0812...`), donation figures containing currency symbols (`Rp`), and donation dates formatted as raw Excel internal serial numbers (`46280`).
- *Solution:* I built an intelligent data normalization layer within the import action. The parser automatically detects and converts Excel numeric serials into valid dates, strips currency formatting into pure integers, standardizes phone numbers into international format (`+62812...`), and returns granular row-level feedback for missing data.

**3. Preventing Message Failures Caused by Telegram Markdown Formatting**
- *Challenge:* Telegram Bot API enforces strict parsing rules for Markdown entities. If an organization or donor name contains unescaped special characters like underscores (`_`), asterisks (`*`), or brackets (`[`), Telegram rejects the API call with a `400 Bad Request` error.
- *Solution:* Beyond standard character escaping, I engineered a graceful fallback mechanism. If Telegram returns an entity parsing error, the driver immediately re-dispatches the exact reminder payload as plain text, guaranteeing that the donor receives the notification without interruption.

---

## Business Value & Key Takeaways

- **Protecting Foundation Cashflow & Donor Retention:** Social programs thrive on consistent donor commitment. Automating friendly, respectful reminders dramatically reduces unintentional donor churn, keeping programs funded and predictable.
- **Saving Hundreds of Operational Staff Hours:** Transitioning from manual phone-by-phone copy-pasting to a hands-free automated pipeline frees up staff to focus on real community outreach, orphan care, and social impact.
- **The Imperative of Resilience Engineering:** True business value in financial and notification applications comes from expecting third-party failures and building self-healing systems: clear error logs, atomic safeguards, and instant one-click recovery tools.
