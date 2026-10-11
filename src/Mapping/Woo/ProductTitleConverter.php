<?php
namespace SuiteEdge\Migrator\Mapping\Woo;
class ProductTitleConverter extends \SuiteEdge\Migrator\Mapping\CommonConverter {
    protected function get_target_type() { return 'woocommerce-product-title'; }
}
