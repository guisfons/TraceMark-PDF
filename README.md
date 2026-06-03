# TraceMark PDF — Case Study

> **Custom WordPress Plugin** · Secure PDF Distribution · Dynamic Watermarking · Role-Based Access Control · Country Reports

![WordPress](https://img.shields.io/badge/WordPress-6.0+-21759B?logo=wordpress&logoColor=white)
![PHP](https://img.shields.io/badge/PHP-8.0+-777BB4?logo=php&logoColor=white)
![MySQL](https://img.shields.io/badge/MySQL-8.0-4479A1?logo=mysql&logoColor=white)
![Composer](https://img.shields.io/badge/Composer-Managed-885630?logo=composer&logoColor=white)
![MIT License](https://img.shields.io/badge/License-MIT-green)

<!-- TODO: Add screenshot of the Country Reports grid (frontend view) here -->

---

## 1. Project Overview

TraceMark PDF is a WordPress plugin engineered for the secure, traceable distribution of confidential PDF documents to a network of authorized business representatives. The plugin provides two structured content systems — **Weekly Bulletins** and **Country Reports** — each backed by a dedicated Custom Post Type, with every document download generating a uniquely watermarked PDF containing the recipient's identity data, creating an auditable chain of custody for sensitive materials.

<!-- TODO: Add screenshot of a watermarked PDF showing the user's company name and email overlaid -->

---

## 2. The Problem

The client — an organization distributing confidential analytical reports and weekly bulletins to a network of country-level business partners — was managing document distribution through email attachments. This created critical security and operational gaps:

- **No access control:** Documents forwarded by recipients were indistinguishable from originals, with no mechanism to identify the source of a leak.
- **No audit trail:** There was no record of who had downloaded which document, when, or how many times.
- **Manual distribution:** Sending updated reports to the correct recipients required manual email composition, creating a bottleneck and introducing the risk of misdistribution.
- **Static documents:** Reports could not be silently updated — every revision required a new email blast, and recipients had no guarantee they were working from the latest version.

---

## 3. The Solution & Architecture

TraceMark PDF transforms WordPress into a secure document distribution portal with dynamic, per-download PDF watermarking. Every document download is intercepted by the plugin's PHP layer, which generates a unique PDF copy on the fly — stamping the recipient's company name, email address, and download timestamp across every page before delivering the file to the browser.

### Document Distribution Architecture

```
Authorized user (Contributor role) logs in
    │
    ▼
Accesses Weekly Bulletins or Country Reports page (shortcode-rendered grid)
    │
    ▼
Clicks download → PHP handler intercepts request
    │
    ├── Access check: is user Contributor or Administrator?
    │       └── No → redirect to login
    │
    ├── Retrieve PDF from secure storage (wp-content/uploads/tracemark-secure/)
    │       (directory protected from direct browser access via .htaccess)
    │
    ├── Load user profile data (company name, company logo, email)
    │
    ├── Generate unique watermarked PDF:
    │       ├── Background watermark: diagonal text (Company + Email), 15% opacity
    │       ├── Footer stamp: Email | Company | Date/Time (Brazil timezone) on every page
    │       └── Logo overlay: company logo centered at 30% opacity
    │
    └── Stream unique PDF to browser (Content-Disposition: attachment)
```

### Custom Post Types

**Weekly Bulletins (`boletim-semanal`):**
- One entry per bulletin, organized by publication date
- Full chronological archive rendered via `[boletins_semanais]` shortcode
- Cards display date, document title, and a download action

**Country Reports (`relatorio-pais`):**
- One report per country — the PDF is replaceable without changing the permanent URL
- `Países` (Countries) taxonomy with support for flag images (URL or Dashicon) per term
- Grid rendered via `[relatorios_pais]` shortcode, grouped by country with last-updated date

### Security Architecture

- **Secure storage:** All PDFs are stored in `wp-content/uploads/tracemark-secure/` — a directory explicitly blocked from direct browser access via `.htaccess` rules.
- **Role-based access:** Only `Contributor` and `Administrator` roles can access the download endpoints. All other users are redirected to the WordPress login page.
- **Per-download uniqueness:** Because watermarks are generated at download time — not upload time — it is impossible for two recipients to hold an identical copy, making leak attribution unambiguous.
- **User profile fields:** A custom profile section (`Logo da Empresa`, `Nome da Empresa`) allows each representative to register their company identity, which the watermark engine reads automatically.

---

## 4. Technologies Used

- **CMS & Backend:** WordPress 6.0+, PHP 8.0+, MySQL
- **PDF Generation:** Composer-managed PHP PDF library (server-side watermark rendering)
- **Access Control:** WordPress Roles & Capabilities API (`Contributor` role gating)
- **Content Architecture:** Two Custom Post Types + `Países` taxonomy
- **Frontend:** Shortcode API — `[boletins_semanais]`, `[relatorios_pais]`
- **Security:** `.htaccess` directory protection, server-side file streaming (no direct URLs)
- **User Management:** Custom WordPress profile fields for company identity

---

## 5. Design Process & UI/UX

The frontend grids were designed with clarity and efficiency as primary goals — the authorized representatives accessing these documents are typically executives or country managers who need to locate and download the correct report with minimal friction. The Country Reports grid uses flag imagery as a visual anchor, allowing users to scan by country identity rather than reading a list of names. The Weekly Bulletins archive uses a date-grouped timeline layout that makes it immediately obvious which bulletin is the most recent.

The watermark design balances visibility with readability: the background diagonal text is semi-transparent to avoid obscuring the document content, while the footer stamp is fully legible, ensuring the traceability data is always recoverable even if a recipient attempts to crop the watermark.

<!-- TODO: Add screenshot of the Weekly Bulletins archive grid here -->
<!-- TODO: Add screenshot of the Country Reports grid with flag thumbnails here -->
<!-- TODO: Add screenshot of the custom user profile fields (Logo + Company Name) in WP admin here -->

---

## 6. Project Outcomes

- **Leak attribution:** Every distributed PDF is uniquely identifiable by recipient. Any document appearing outside the authorized network can be traced back to its source immediately.
- **Access control:** The Contributor role gate ensures that only registered, authenticated representatives can download documents — eliminating the possibility of unauthorized access from the public web.
- **Distribution automation:** Representatives access the latest version of each country report through a permanent URL that the administrator can update without contacting recipients — ending the manual email distribution cycle.
- **Audit capability:** WordPress's post meta and user logs provide a queryable record of document versions and access events.
- **Zero infrastructure overhead:** The entire solution operates within the existing WordPress installation — no external storage services, CDN configuration, or third-party DRM licensing required.
