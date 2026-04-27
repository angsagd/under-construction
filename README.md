# Bak Pasir Under Construction

Bak Pasir is a playful under-construction page. Instead of showing a static notice, the landing page invites visitors to help build the site through a collection of small browser games.

Each game turns a familiar web-development task into a quick interaction: collecting tools, getting code, avoiding errors, building a website tower, fixing bugs, typing commands, setting a deployment, and putting the countdown in order. The project is intentionally lightweight and runs with plain HTML, CSS, and JavaScript.

## Pages

- `index.html` is the main under-construction landing page.
- `tools.html` is a catch-the-falling-tools game.
- `code.html` is a code-themed snake game.
- `error.html` is a 404 runner game.
- `website.html` is a tower-stacking game.
- `bugs.html` is a bug-clicking game.
- `command.html` is a command typing challenge.
- `deploy.html` is a deploy timing challenge.
- `countdown.html` is a sliding countdown puzzle.

## Project Structure

- `css/` contains the stylesheets for each page.
- `js/` contains the game logic and landing page animation.
- `data/` contains JSON data used by some games.
- `img/` is reserved for image assets.

## Running Locally

Open `index.html` directly in a browser, or serve the folder with any static web server. A local server is recommended because some games load JSON files from the `data/` directory.

Example:

```bash
python3 -m http.server 8000
```

Then visit:

```text
http://localhost:8000
```
