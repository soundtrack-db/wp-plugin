# SoundtrackDB for WordPress

<p align="left">
  <img src="https://img.shields.io/badge/WordPress-5.0+-21759b?style=flat-square&logo=wordpress" alt="WordPress">
  <img src="https://img.shields.io/badge/PHP-7.4+-777bb4?style=flat-square&logo=php" alt="PHP">
  <img src="https://img.shields.io/badge/License-GPLv2-green?style=flat-square" alt="License">
  <a href="https://github.com/soundtrack-db/wp-plugin/raw/main/soundtrackdb-wp.zip"><img src="https://img.shields.io/badge/Download-Plugin%20.zip-22c55e?style=flat-square" alt="Download Zip"></a>
</p>

The official WordPress plugin for **[SoundtrackDB](https://soundtrackdb.vercel.app/)** — embed verified Spotify movie & TV soundtrack playlists on any post or custom post type with zero API keys required.

Theme-agnostic: works with **any WordPress theme** that stores a TMDB or IMDb ID in custom fields / post meta.

---

## ⚡ Quick Download

Download the ready-to-upload zip directly:
👉 **[Download soundtrackdb-wp.zip](https://github.com/soundtrack-db/wp-plugin/raw/main/soundtrackdb-wp.zip)**

---

## 🚀 Features

- **Zero Credentials**: Powered by the free SoundtrackDB public API (100 req/min). No Spotify or SoundtrackDB developer accounts needed.
- **Theme & Importer Agnostic**: Works with movie database importers (Dooplay, Toroplay, WP-Script, custom themes) — just tell it your existing meta field name (e.g. `tmdb_id` or `imdb_id`).
- **Smart 24-Hour Transients Cache**: Lookups are cached locally in your WordPress database. Even if a post receives 100,000 visitors, your site only queries the API once per day per title.
- **Auto-Insert or Shortcode**: Automatically display the soundtrack before/after post content, or drop the `[soundtrackdb]` shortcode anywhere.
- **Graceful Fallbacks**: If no soundtrack is found for a title, nothing renders — zero errors or broken frames are shown to your visitors.

---

## 📦 Installation

### Method 1: Upload via WordPress Admin (Recommended)
1. Download **[`soundtrackdb-wp.zip`](https://github.com/soundtrack-db/wp-plugin/raw/main/soundtrackdb-wp.zip)**.
2. In your WordPress Admin dashboard, navigate to **Plugins ➔ Add New ➔ Upload Plugin**.
3. Choose `soundtrackdb-wp.zip` and click **Install Now**.
4. Click **Activate Plugin**.

### Method 2: Manual FTP Upload
1. Extract the `soundtrackdb-wp.zip` file.
2. Upload the `soundtrackdb-wp` folder to your `/wp-content/plugins/` directory.
3. Activate the plugin through the **Plugins** menu in WordPress.

---

## ⚙️ Configuration

Go to **Settings ➔ SoundtrackDB** in your WordPress Admin:

| Setting | Default | Description |
| :--- | :--- | :--- |
| **TMDB ID meta key** | `tmdb_id` | The custom field name your theme uses for TMDB ID (e.g. `tmdb_id`, `id_tmdb`). |
| **IMDb ID meta key** | `imdb_id` | Fallback custom field name for IMDb ID (e.g. `imdb_id`). |
| **Auto-insert widget** | `Disabled` | Check to automatically insert the soundtrack on single posts with an ID. |
| **Insert position** | `After content` | Choose whether to display before or after `the_content`. |
| **Cache lookups** | `24 hours` | How long to cache API results in WordPress transients. |

---

## 📝 Shortcode Usage

You can use the shortcode anywhere inside posts, pages, or page builders (Elementor, Gutenberg, Divi):

```text
[soundtrackdb]
```
Automatically reads the current post's TMDB or IMDb ID from its custom fields.

### Override with Specific IDs
```text
[soundtrackdb tmdb_id="993710"]
```
```text
[soundtrackdb imdb_id="tt15239678"]
```

---

## 🔗 Related Resources

- 🌐 **SoundtrackDB Web App:** [soundtrackdb.vercel.app](https://soundtrackdb.vercel.app/)
- 📖 **API Documentation:** [soundtrackdb.vercel.app/docs](https://soundtrackdb.vercel.app/docs)
- 📦 **API GitHub Repository:** [github.com/soundtrack-db/api](https://github.com/soundtrack-db/api)

---

## 📄 License

This plugin is distributed under the [GPL-2.0 License](./LICENSE).
