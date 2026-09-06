=== SoundtrackDB Integration ===
Contributors: cnf1g, shreyash
Tags: movies, tv shows, spotify, soundtrack, tmdb
Requires at least: 5.0
Tested up to: 6.6
Requires PHP: 7.4
Stable tag: 1.0.0
License: GPLv2 or later

Show the Spotify soundtrack for any movie/TV post, using the TMDB or IMDb ID your site already stores.

== Description ==

SoundtrackDB Integration adds a "Listen to the Soundtrack" widget to your
movie/TV posts, powered by the free SoundtrackDB API
(https://soundtrackdb.vercel.app/). No API key required.

It does NOT assume any specific theme. Instead, it reads whatever custom
field your site already uses to store a TMDB or IMDb ID — just tell it the
field name once in Settings -> SoundtrackDB, and it works on any post type,
any theme.

== Installation ==

1. Upload the `soundtrackdb-wp` folder to `/wp-content/plugins/`, or upload
   the zip via Plugins -> Add New -> Upload Plugin.
2. Activate the plugin.
3. Go to Settings -> SoundtrackDB and set the custom field name your site
   uses for TMDB ID (and optionally IMDb ID as a fallback).
4. Either:
   a) Enable "Auto-insert widget" to show it automatically on every post
      that has a TMDB/IMDb ID, or
   b) Add the shortcode `[soundtrackdb]` manually wherever you want it to
      appear.

== Shortcode usage ==

`[soundtrackdb]` — uses the current post's TMDB/IMDb ID automatically.
`[soundtrackdb tmdb_id="993710"]` — force a specific TMDB ID.
`[soundtrackdb imdb_id="tt21192188"]` — force a specific IMDb ID.

== Notes ==

- Lookups are cached (default 24h) using WordPress transients, so your site
  stays fast and stays well within the API's free rate limit.
- If no soundtrack is found for a title, the widget simply doesn't render —
  no errors shown to visitors.
- This plugin only reads existing post meta; it does not scrape or import
  content from any source.
