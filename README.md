# amazonWishlistWidget
A Wordpress widget to display an Amazon wishlist

The Amazon API does not allow for getting a list of items on a wish list, so this widget uses simple screen scraping to pull the data. It's a single file, and creates a dashboard control panel for entering the wishlist ID (the URL for a wish list is "http://www.amazon.com/gp/registry/wishlist/{listID}/other-stuff"; just copy the listID part).

You can also filter by product type, and select the number of items to show, and the height of the image (in pixels); set that to zero to not show an image. You can also set a tag for creating links that go to your Amazon Associates account.

It presents a random selection from the wish list, with the image, title and author, all in a link back to Amazon.
