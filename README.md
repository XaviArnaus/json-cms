# A JSON & PHP based CMS
This is a project to have a basic CMS that runs under the following premises:
- Must not rely on a DB
- Must rely on JSONL files
- Must be quick and fast to set up
- Must generate a set of HTML files
- Should have an easy to use admin section to post and mantain the site.

# Brief explanation
This CMS consists out of 2 main parts:
- The HTML Renderer
- The CMS interface

## The HTML Renderer
This is a PHP scripts that:
1. Reads all JSONL files
1. Loads all needed templates
1. Builds then the HTML files

You can trigger this action by executing the following command:
```
php render.php
```
... or you can also:
```
make render
```

## The CMS interface
This is an interface like in all other CMSs, where the user has a set of tools to post / edit / delete content, and to perform the maintenance of the site.

This is currently Work in progress.

# How to set it up

## Requirements
- You need a HTTP server like Apache that can process PHP files
- You need to have a `vhost` already set it up to point the HTTP traffic to your working directory.

## Steps
1. Clone the repository in the workind directory or you can simply copy all the files in your working directory.
1. Create a new `config.json` file in the root of the working directory and edit according to your needs. You can use the `config.json.example` as a start. Read the section below to get more info regarding it.
1. Point your `vhost` to the `public_html` directory
1. Render the site as explained above. Remember that without content, the site can look weird.

## Understanding the config.json
The `config.json` is the main configuration place for the CMS and contains all the settings and site parameters. Here is an example:

```
{
  "last_update": "2020-01-02 06:00:00",
  "db_path": "json_dbs/%s_%s.jsonl",
  "public_path": "public_html",
  "slug_latest": "latest",
  "entities_path": "entities",
  "filename_template": "generated_at_%s_%s.json",
  "display_template": "default",
  "entities": [
    {"name": "Post", "collection": "posts", "db_slug": "posts", "template": "post"}
  ],
  "render_profiles": [
    {"name": "home", "slug": "index", "sorting": "date", "grouping": "feed", "template": "home"},
    {"name": "posts", "slug": "posts", "sorting": "date", "grouping": "entity", "template": "posts"}
  ],
  "layout": {
    "metadata": {
      "site_url": "http://mysite.com",
      "title": "This is my site",
      "description": "This an awesome JSON & PHP site",
      "favicon": "img/favicon.png",
      "css": [
        "css/bootstrap.min.css",
        "css/responsive.css",
        "css/style.css",
        "css/font-awesome.min.css",
        "vendors/linericon/style.css",
        "vendors/flaticon/flaticon.css"
      ],
      "js": [
        "js/jquery.min.js",
        "js/bootstrap.bundle.min.js"
      ]
    }
  }
}
```
### Entities
The entities are each content type able to be published in the site. By now are only *Posts*, next ones will be *Pictures*.

The entities are set up in the `config.json` by specifying the following parameters:
- `name`: Name of the entity. Also used as a link to the related PHP object.
- `collection`: Name of the collection of these entities. Usually the plural.
- `db_slug`: Slug that will be used to relate to the JSONL file that contains this kind of entities.
- `template`: template slug that will be used to relate to the templates of this kind of entities.

The entity also relies in a PHP object class with the same name (first letter as Uppercase), usually located in `/entities`.

Finally, don't forget to add the related template files. You'll need basically:
- A main page for the entity items: `[collection].html`
- A listing entity item for the lists: `listed_[name].html`
- A page for the full entity display: `[name].html` (Work in progress)

### Render Profiles
A render profile is nothing more than the main sections of your site. You'll have a *Home* and also one section for every entity you have set up.

For example, having the entity "Post" set up, you'll have the *home* and the *posts* render profiles.

Every render profile has the following parameters in the `config.json`:
- `name`: Name of the render profile. This is usually the same of the entity collection literal. Will also be used in the navigation links.
- `slug`: This is the filename that will be used when generating the HTML. Note that the `.html` extension will be added internally.
- `sorting`: This is the sorting strategy for this render profile. Currently not developed, as it will be rendered as it comes from the JSONL file.
- `grouping`: This is the grouping strategy for this render profile. The idea is that the data is grouped by entity type so that it can be allocated into the page and show easily lists of entities. This corresponds to the `entity` value. There is a `feed` value that merges all the entities, so that one could show an overall activity on the site, and it's intended for the *home* render profile.
- `template`: The themplate used by this render profile to render the page.

# ToDo
- Develop the Admin part of the CMS.
- Add an "About Us" and a "Contact" render profiles.
- Extend the documentation.