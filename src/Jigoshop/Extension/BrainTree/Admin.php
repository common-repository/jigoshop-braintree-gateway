<?php
namespace Jigoshop\Gateway\BrainTree;

/**
 * Class Admin
 */
class Admin
{
    public function __construct()
    {
        add_filter('plugin_action_links_' . plugin_basename(JIGOSHOP_BRAINTREE_GATEWAY_DIR . '/bootstrap.php'),
            array($this, 'actionLinks'));
    }

    /**
     * @param array $links
     * @return array
     */
    public function actionLinks($links)
    {

        $links[] = '<a href="https://wordpress.org/support/plugin/jigoshop-ecommerce/#new-post" target="_blank">Support</a>';
        $links[] = '<a href="https://wordpress.org/support/plugin/jigoshop-ecommerce/reviews/#new-post" target="_blank">Rate Us</a>';
        $links[] = '<a href="https://www.jigoshop.com/product-category/extensions/" target="_blank">More plugins for Jigoshop</a>';
        return $links;
    }
}

new Admin;