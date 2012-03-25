Pjaxy
=====

Pjaxy is a library to make using [PJAX](https://github.com/defunkt/jquery-pjax) with your WordPress themes a bit easier.

PJAX combines partial page reloads with HTML5 pushState to give your users a much faster site with a working back button.

## How to Use Pjaxy

First, you'll need to get the library into your theme folder.

    $ cd /your/current/theme/directory
    $ git clone git://github.com/chrisguitarguy/pjaxy.git
    $ cd pjaxy
    $ git submodule update --init

After you have the code, you'll need to tell your theme to use it.  In `functions.php`:

    require_once( trailingslashit( get_template_directory() ) . 'pjaxy/load.php' );

Now simply add theme support for pjax.  You'll also need to define a few constants to tell the pjaxy library which container to update on pjax request.

    add_action( 'after_setup_theme', 'mytheme_setup' );
    function mytheme_setup()
    {
        add_theme_support( 'pjax' );

        // the css selector for the container pjaxy is to update
        define( 'PJAXY_CONTAINER', '#main' );

        // Does your theme use custom header images? Tell Pjaxy where to update those
        define( 'PJAXY_HEADER_IMG', 'header#branding > a img' );
    }

Alternatively, if you would like to role your own JavaScript to enable PJAX, don't define any constants for Pjaxy.

## How it Works

PJAX works by reloading a part of the page. The idea, of course, is that you don't need to send the entire page over the web to the client every time.

Pjaxy works by hijacking the various `template_include` filters and returning templates that are not complete pages.

## Creating your PJAX Templates

Assuming you've done the steps above to get Pjaxy working, you'll now need to create your PJAX templates.

First, Create a `pjax-templates` folder in your theme. This is where your PJAX templates will reside.

If you've already created your theme, creating a PJAX template is simple.  Open up one of the main theme files (like `index.php`), grab the contents within container that PJAX will update, and paste it into `pjax-templates/index.php`. Alternatively, you could use [template parts](http://codex.wordpress.org/Function_Reference/get_template_part)

Pjaxy will look for templates in the same way that WordPress does: check the child theme directory in `pjax-templates`, then the parent theme, and then fail.  If pjaxy does not fide a template, the page will use the the normal template.  This will cause the PJAX library to do a "hard" reload of the page -- eg. no partial reload or pushState.

Let's say your make a request for a single post. Pjaxy first looks in the childtheme directory in `pjax-templates` for `single.php`, the in the parent theme directory for `pjax-templates/single.php`. Same deal for taxonomy pages (including tags and categories).

If you plan on using the built in JS to enable PJAX, just make sure to call `get_pjaxy_page_info` ([pjax.php](https://github.com/chrisguitarguy/pjaxy/blob/master/pjax.php#L43)) at the top of each PJAX template.  This function puts some stuff on the page to change the body class, page title, and header image.

Check out this [twenty eleven child theme](https://github.com/chrisguitarguy/pjaxy-example) for an example.

## License

jQuery PJAX is copyright [Chris Wanstrath](https://github.com/defunkt) and licensed under the MIT license.  The code unique to the pjaxy library is licensed under the GPLv2, just like WordPress.
