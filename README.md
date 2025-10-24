# 🧠 LeetCode Stats — Live LeetCode Profile Dashboard

A lightweight, elegant web app to visualize **LeetCode** stats with charts, KPIs, and a ready‑to‑share PNG card.  
Built with **PHP**, **TailwindCSS**, **Chart.js**, and **HTML2Canvas**.

🌐 Live demo: **https://wherepanda.xyz/leetcode**  
Example user: **https://wherepanda.xyz/leetcode?user=wherePANDA**

---

## ✨ Features

- 📊 Difficulty breakdown (Easy / Medium / Hard) with totals & per‑difficulty submissions
- 📈 Global acceptance rate and ranking
- 🧾 Responsive table + KPI strip + sidebar summary
- 🥧 Donut charts (main view + shareable card) powered by Chart.js
- 🖼️ One‑click **PNG** card generation (HTML2Canvas)
- 🔗 Social share buttons (X/Twitter, LinkedIn, WhatsApp, Telegram)
- ⚡ Fast client fetch → **`leetcode_api.php`** (GraphQL to leetcode.com)
- 🧊 Simple file cache per user (3 hours) to reduce API calls

---

## 🧰 Tech Stack

- **PHP** (endpoint & templating)
- **TailwindCSS** (UI)
- **Chart.js** (charts)
- **HTML2Canvas** (export to PNG)
- **Font Awesome** (icons)
- **Google Fonts (Inter)**

---

## 📦 Project Structure

```
.
├─ leetcode.php         # UI (dashboard, charts, shareable card)
├─ leetcode_api.php     # Backend endpoint (GraphQL → JSON) with file cache
└─ README.md
```

> Place both `.php` files in your web root (or serve locally with `php -S`).

---

## 🚀 Quick Start

**Option A — Local PHP server**

```bash
php -S localhost:8080
# then open:
# http://localhost:8080/leetcode.php?user=yourLeetCodeUser
```

**Option B — Any PHP hosting**  
Upload `leetcode.php` and `leetcode_api.php` to your document root and visit:
```
/leetcode.php?user=yourLeetCodeUser
```

---

## 🔌 How It Works

### 1) `leetcode.php` (frontend)
- Reads the `user` query param (`?user=...`), sanitizes with `htmlspecialchars`.
- Calls the backend endpoint:
  ```js
  fetch(`leetcode_api.php?user=${encodeURIComponent(username)}`)
  ```
- Renders KPIs, difficulty cards, acceptance rates, a donut chart, table & sidebar.
- Builds a **hidden shareable card** and exports it to **PNG** via HTML2Canvas.
- Provides native share links for X/Twitter, LinkedIn, WhatsApp and Telegram.
- Shows a loading state and an error card with *Retry* on failures.

### 2) `leetcode_api.php` (backend)
- Validates `?user` and returns **JSON** or an error payload.
- Caches responses per user (`cache_$user.json`) for **3 hours**.
- Queries `https://leetcode.com/graphql` with:
  ```graphql
  {
    matchedUser(username: "USERNAME") {
      username
      profile { realName ranking reputation countryName userAvatar }
      submitStats { acSubmissionNum { difficulty count submissions } }
    }
  }
  ```
- Normalizes the response to:
  ```json
  {
    "username": "wherePANDA",
    "name": "John Doe",
    "avatar": "https://assets.leetcode.com/users/...",
    "ranking": 12345,
    "country": "Spain",
    "reputation": 100,
    "stats": {
      "easy":   { "solved": 123, "submissions": 150 },
      "medium": { "solved": 45,  "submissions":  60 },
      "hard":   { "solved": 12,  "submissions":  30 }
    }
  }
  ```

---

## 🧮 Calculations

- **Per‑difficulty acceptance** = `round(solved / submissions * 100)`  
- **Global acceptance** = same formula over the **sum** of all solved & submissions.  
- **Progress bars** = proportion of solved by difficulty over total solved.

---

## 🛡️ Security & Reliability Notes

- **XSS‑safe** input for username (PHP `htmlspecialchars` with `ENT_QUOTES`).
- **CORS**: `Access-Control-Allow-Origin: *` in `leetcode_api.php`.
- Friendly **User‑Agent** on cURL.
- Fallback avatar (LeetCode logo) if profile has none.
- Robust **loading & error states** on the frontend.
- **File cache** (3h TTL) to reduce API calls / rate‑limiting and speed up loads.

> On shared hosting, ensure the process can **write** cache files in the project directory.

---

## 🧪 Quick API Test

```bash
curl "http://localhost:8080/leetcode_api.php?user=wherePANDA"
```

You should receive a JSON payload with `username`, `name`, `avatar`, `ranking`, `country`, `reputation`, and `stats` (easy/medium/hard solved + submissions).

---

## 🖼️ Screenshots (optional)

Add your own screenshots and reference them here, e.g.:
```markdown
![Dashboard](assets/preview-dashboard.png)
![Shareable Card](assets/preview-card.png)
```

---

## 🗺️ Roadmap Ideas

- [ ] Dark mode 🌙  
- [ ] History / streaks over time  
- [ ] Compare multiple users  
- [ ] i18n (multi‑language UI)

---

## 🤝 Contributing

Issues and PRs are welcome. Please include a clear description and screenshots for UI changes.

---

## 🪪 License

Released under the **MIT License**.
