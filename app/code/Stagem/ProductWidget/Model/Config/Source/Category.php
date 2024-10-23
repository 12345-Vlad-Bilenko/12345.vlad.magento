<?php
namespace Stagem\ProductWidget\Model\Config\Source;

use Magento\Framework\Data\OptionSourceInterface;
use Magento\Catalog\Model\ResourceModel\Category\CollectionFactory;

class Category implements OptionSourceInterface
{
    protected $categoryCollectionFactory;

    public function __construct(CollectionFactory $categoryCollectionFactory)
    {
        $this->categoryCollectionFactory = $categoryCollectionFactory;
    }

    public function toOptionArray()
    {
        $categories = $this->categoryCollectionFactory->create()
            ->addAttributeToSelect('name')
            ->addIsActiveFilter();
        
        $options = [];
        foreach ($categories as $category) {
            $options[] = ['value' => $category->getId(), 'label' => $category->getName()];
        }

        return $options;
    }
}