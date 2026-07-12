# HIGH COST BILLING SYSTEM
## Product Overview & Pricing Proposal

**Prepared for:** [Hospital / Facility Name]  
**Prepared by:** [Your Company Name]  
**Date:** [Insert Date]  
**Valid until:** [Insert Date + 30 days]

---

## 1. Executive Summary

The **High Cost Billing System** is a web-based hospital billing and patient management platform built for health facilities that run **prepaid member schemes**, **dependant accounts**, **company-sponsored patient pools**, and **pay-as-you-go (casual caller) patients**.

It replaces manual receipt books, spreadsheets, and disconnected records with one secure system where Registry, Consultant, Accounts, and Administration staff each see only what they need to do their job.

This proposal outlines what the system includes, how it works, and our **affordable founding-partner pricing** for facilities entering digital billing for the first time.

---

## 2. The Problem We Solve

Many hospitals and clinics in Zambia still manage high-cost patient billing using:

- Paper receipt books and duplicate copies  
- Excel sheets for member balances  
- No clear record of who changed a balance or voided a bill  
- Delays between registration, consultation, and payment posting  
- Difficulty tracking dependants billed against a principal member  
- No proper statement for members asking “what happened to my deposit?”  
- Walk-in patients (not on scheme) handled outside the main billing process  
- Manual hospital file numbers prone to duplicates and errors

The High Cost Billing System solves these problems with a single, auditable platform designed for **real hospital workflows**.

---

## 3. Who the System Is For

| Facility type | Typical use |
|---|---|
| Mission / NGO hospitals | Member schemes, dependants, company patients, casual callers |
| Private clinics with prepaid accounts | Deposits, visit billing, receipts |
| Hospital sections (e.g. High Cost) | Separate billing pool from main hospital |
| Company health schemes | Shared company deposit pool per employer |
| Mixed patient populations | Scheme members and same-day cash patients in one system |

---

## 4. Patient Types

The system supports **four patient categories**, each with its own billing rules:

| Patient type | Has membership? | Has deposit balance? | Who pays? |
|---|---|---|---|
| **Individual Member** | Yes | Yes — own account | The member |
| **Dependant** | Yes (under principal) | Uses principal's balance | Principal member |
| **Company Patient** | No | Uses company pool | Employer company |
| **Casual Caller** | No | No — pay as you go | Patient pays immediately at Accounts |

### Casual Caller workflow

For patients who are not on the High Cost Scheme and not covered by a company:

1. **Registry** registers patient as Casual Caller (hospital file number assigned automatically, e.g. `RRGH-000356`)  
2. **Consultant** records clinical notes  
3. **Registry** posts billable services  
4. **Accounts** issues the bill and collects payment (Cash, Mobile Money, etc.)  
5. **Receipt** printed — visit closed  

Casual callers do not maintain a running deposit account. Their history is tracked as **visit-by-visit** on the patient profile and in the **Casual Caller Report**.

---

## 5. System Modules & Features

### 5.1 Patient Management

- Register **Members**, **Dependants**, **Company Patients**, and **Casual Callers**
- **Auto-generated hospital file numbers** (e.g. `RRGH-000123`) — staff cannot type duplicates
- Auto-generated patient numbers and membership numbers for scheme members
- Search by name, patient number, file number, membership number, NRC, MAN number, or phone
- Patient profiles with balance (or pay-as-you-go status), membership status, and activity
- Link dependants to their principal (paying) member — dependants inherit the principal's membership number
- Link company patients to employer company accounts

### 5.2 Visit & Clinical Workflow

End-to-end patient visit tracking:

1. **Open visit** (Registry)  
2. **Payment / membership check** — system blocks consultation for scheme patients if account is not funded (casual callers proceed directly)  
3. **Consultant consultation** — queue, active visits, clinical notes  
4. **Charge posting** (Registry) — add billable services from catalogue  
5. **Bill posting** — deduct from member/company balance, or issue invoice for casual caller  
6. **Payment & receipt** — immediate collection at Accounts for casual callers; receipt for all bill types

**Visit types supported:** OPD, IPD, Emergency

**Clinical notes:** Complaint, vitals, examination, diagnosis, treatment, and follow-up recorded by the consultant

### 5.3 Financial Management (Accounts)

- **Member deposits** — load prepaid treatment money with reference and receipt
- **Company deposits** — load employer company pools
- **Membership fees** — record scheme subscription payments and track expiry
- **Casual caller collections** — record immediate payment at the desk after bill is issued
- **Account ledger** — bank-style running balance for members and companies (every deposit, bill, void, and reversal)
- **Deposit reversal** — controlled reversal with audit trail
- **Large deposit confirmation** — extra checkbox for high-value deposits
- **Low balance alerts** — warning when payer balance is running low
- **Outstanding casual caller bills** — Accounts dashboard lists unpaid pay-as-you-go invoices

**Payment methods recorded:** Cash, Mobile Money, Bank Transfer, Cheque, Card

### 5.4 Billing & Receipts

- Post bills from completed visits
- Automatic balance deduction for members, dependants (via principal), and company patients
- Pay-as-you-go billing for casual callers — no balance deduction; payment collected at Accounts
- Printable receipts for bills, deposits, and membership payments
- Bill voiding with balance restoration (Registry, with audit log); unpaid casual caller bills can be voided without balance adjustment
- Insufficient balance handling with staff confirmation (scheme patients only)

### 5.5 Reports & Exports

All major reports export to **CSV** (Excel) and **PDF**:

| Report | Description |
|---|---|
| Financial Summary | Deposits, bills, voids, and casual caller totals for selected period |
| Transaction Detail | Full transaction list with filters |
| Member Accounts | Balances and activity per member |
| Patient Statement | Bank-style ledger statement per member or dependant |
| Company Reports | Pool usage per company |
| **Casual Caller Report** | Pay-as-you-go bills, collections by payment method, outstanding amounts |
| Audit Log | Who did what, and when |

Casual callers use the **Casual Caller Report** and **visit history on the patient profile** instead of a running account statement.

### 5.6 Dashboards (Role-Based)

Each staff role sees a tailored dashboard with KPIs and charts:

| Role | Dashboard focus |
|---|---|
| **Registry Clerk** | Patient flow, registrations, pending workload |
| **Consultant** | Consultation queue, patients seen, diagnoses |
| **Accounts Officer** | Revenue, deposits, billing activity, outstanding casual caller bills |
| **Administrator** | System activity, user activity, audit events |

### 5.7 Administration

- **Staff user management** — create accounts, assign roles, activate/deactivate
- **Service catalogue** — manage billable services and fixed prices
- **System settings** — hospital name, section name, file number prefix, billing thresholds
- **Full audit trail** — every patient, deposit, bill, and settings change logged
- **Role-based access** — staff only see modules for their job

### 5.8 Security

- Secure login with session timeout
- Role-based permissions (Administrator, Accounts, Registry, Consultant)
- Inactive staff accounts cannot log in
- Complete audit log for accountability
- CSRF protection and encrypted passwords

---

## 6. Staff Roles

The system comes with four built-in roles:

| Role | Main responsibilities |
|---|---|
| **Registry Clerk** | Register patients (all four types), open visits, post charges and bills |
| **Consultant** | Consultation queue, clinical notes, patient care workflow |
| **Accounts Officer** | Deposits, membership fees, company accounts, casual caller collections, reports, receipts |
| **Administrator** | Staff users, service catalogue, system settings, audit logs |

Each user logs in with their own username and sees only their relevant menu.

---

## 7. What Is Included in the Package

### Standard Founding Partner Package

| Item | Included |
|---|---|
| Full system license (1 facility) | ✓ |
| All modules listed in Section 5 | ✓ |
| All four patient types (including Casual Caller) | ✓ |
| Up to **10 staff user accounts** | ✓ |
| Installation on your server or ours | ✓ |
| Basic staff training (1 session, up to 4 hours) | ✓ |
| Hospital name & branding in system | ✓ |
| 30 days post-installation support (email / WhatsApp) | ✓ |
| Software updates during support period | ✓ |

### Not Included (Available on Request)

| Item | Notes |
|---|---|
| Computer hardware | Facility provides PCs/laptops |
| Internet connection | Facility responsibility |
| Ongoing support after 30 days | Optional annual plan |
| On-site training beyond first session | Quoted separately |
| Additional facilities / branches | Quoted separately |

### Future Enhancements (Quoted Separately)

The standard package records how a patient paid (Cash, Mobile Money, Bank Transfer, etc.) at the Accounts desk. **Live integrations** with external services are not included in the founding-partner license and are scoped and priced per project.

| Enhancement | Notes |
|---|---|
| **Mobile money integrations** | Direct payment via MTN Mobile Money, Airtel Money, Zamtel Kwacha, or similar — API setup, reconciliation, and go-live quoted separately per provider |
| NHIMA integration | National health insurance claims and eligibility |
| ZRA / fiscal receipts | Tax-compliant fiscal device or e-fiscal integration |
| SMS notifications | Appointment, balance, or payment alerts to patients |
| Custom reports or features | Any facility-specific workflow beyond the standard modules |

*We are happy to discuss a roadmap after go-live. Pricing depends on provider APIs, scope, and testing requirements.*

---

## 8. Pricing

### Founding Partner — One-Time License

We are offering an introductory price to early partner facilities helping us build credibility across Zambia.

| Package | Price (ZMW) |
|---|---|
| **High Cost Billing System — Full License** | **K 15,000 – K 20,000** (once-off) |

**List price:** K 20,000  
**Founding partner price:** from K 15,000  
*Final price depends on facility size, number of users, and installation arrangement. We are open to discussion.*

> **What you pay once:** Permanent license for one facility. No monthly software rental required.

### Optional Add-Ons

| Service | Indicative Price (ZMW) |
|---|---|
| Extended on-site training (extra day) | K 3,000 – K 5,000 |
| Managed hosting (we host for you) | K 1,500 – K 2,500 / month |
| Annual support & updates (after first 30 days) | K 5,000 / year |
| Additional staff users (beyond 10) | K 500 per user (once-off) |
| Future enhancements (e.g. mobile money APIs) | Quoted per project — see Section 7 |

---

## 9. Example: What K 18,000 Gets You

| Item | Amount |
|---|---|
| System license | K 18,000 |
| Installation & basic training | Included |
| 30 days support | Included |
| **Total Year 1** | **K 18,000** |

Compare this to the cost of billing errors, lost receipts, and staff hours spent reconciling balances manually — most facilities recover the investment within the first few months.

---

## 10. Implementation Process

| Step | Timeline | Activity |
|---|---|---|
| 1. Agreement | Day 1 | Sign proposal, confirm price, pay deposit (50%) |
| 2. Setup | Days 2–5 | Install system, configure hospital name, file prefix, and settings |
| 3. Training | Days 5–7 | Train Registry, Consultant, Accounts, and Admin staff |
| 4. Go-live | Day 7–10 | Start using system with live patients |
| 5. Support | Days 10–40 | 30 days post-go-live support |
| 6. Balance payment | Day 30 | Remaining 50% on successful go-live |

**Estimated total setup time:** 7–10 working days from signed agreement.

---

## 11. Technical Requirements

The facility (or we, if hosting) needs:

| Requirement | Minimum |
|---|---|
| Server / PC | 1 machine to host the system (or cloud server) |
| Operating system | Windows Server, Linux, or cloud VPS |
| Database | PostgreSQL 14+ |
| Browser | Chrome, Edge, or Firefox (latest) |
| Network | Local network; internet optional if self-hosted on LAN |
| Staff devices | 1+ PC/laptop per desk (Registry, Accounts, Consultant) |
| Printer | Standard printer for receipts and reports |

*We can advise on the most affordable hosting setup for your facility during the demo.*

---

## 12. Why Choose This System

| Benefit | Detail |
|---|---|
| **Built for Zambia** | Priced for local facilities, kwacha billing, practical workflows |
| **Four patient types** | Members, dependants, company patients, and casual callers in one system |
| **Prepaid scheme ready** | Members, dependants, and company pools — not generic clinic software |
| **Pay-as-you-go ready** | Casual callers billed and paid on the spot — no separate cash book |
| **Accountability** | Full audit log — know who changed every balance |
| **Bank-style statements** | Members can see every deposit and charge |
| **Auto file numbers** | Hospital chart numbers generated automatically — no duplicates |
| **Role-based** | Each staff member sees only their work — less confusion |
| **Export reports** | PDF and CSV for management and auditors |
| **Affordable** | Founding partner pricing from K 15,000 — no expensive monthly fees |
| **Proven workflow** | Developed from real hospital high-cost billing operations |

---

## 13. Terms & Conditions (Summary)

1. **License** — One-time payment grants perpetual use at one facility. Founding partner rate is locked for the signing facility.
2. **Payment** — 50% on signing, 50% within 30 days of go-live unless otherwise agreed.
3. **Support** — 30 days included support covers bug fixes and usage questions. Does not include new feature development.
4. **Training** — One training session (up to 4 hours) included. Additional visits charged separately.
5. **Data** — Facility owns all patient and financial data entered in the system.
6. **Confidentiality** — We do not share facility data with third parties.

*Full terms available on request.*

---

## 14. Next Steps

1. **Book a free demo** — We walk your team through the system (30–45 minutes).
2. **Receive formal quotation** — Fixed price within K 15,000 – K 20,000 range.
3. **Sign & pay deposit** — We begin installation within 5 working days.
4. **Go live** — Your facility becomes a founding partner reference.

---

## 15. Contact Us

| | |
|---|---|
| **Company** | [Your Company Name] |
| **Contact person** | [Your Name] |
| **Phone / WhatsApp** | [+260 XXX XXX XXX] |
| **Email** | [your@email.com] |
| **Location** | [City, Zambia] |

---

*Thank you for considering the High Cost Billing System. We look forward to partnering with your facility.*

**[Your Company Name]**  
*Affordable hospital billing for Zambia*

---

### Document reference: HCB-PROPOSAL-2026-v2
