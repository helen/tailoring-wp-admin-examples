# Custom Tailoring the WordPress Admin Experience

This repository contains code examples for some of the examples contained in various presentations I've given. Rather than publishing static code examples or individual Gists, this will allow the code to continue on as an ever-improving and easy-to-view collection of examples in the realm of tailoring the WordPress admin. Together with [some](http://wordpress.tv/2013/08/11/helen-hou-sandi-custom-tailoring-the-wordpress-admin-experience/) [videos](http://www.youtube.com/watch?v=krL7SXu0YSc), [slide](http://hyhs.me/wcsf2013) [decks](http://hyhs.me/wpnyc2013), and (currently in-progress) blog post, I hope that this will serve as a helpful resource for all those interested.

## Structure and Style

Each example will be in its own folder; if something needs a subfolder, it probably has grown beyond being an example. Each example file will contain an opening comment with a description and URLs of relevant slides, images, and/or other links.

Some files will contain a class that is not a true object but rather a container/namespace. These classes will be instantiated into a global for ease of unhooking; if you would rather these be prefixed global functions or the class be a singleton, or you're really into anonymous functions, you are welcome to modify the code to fit your needs.

## Support

These examples are created with the goal of jump-starting developers in the creation of tailored admin experiences. I am personally unable to provide direct help with implementation.

## Contributing

Pull requests for correction of errata and additional improvements happily encouraged.
