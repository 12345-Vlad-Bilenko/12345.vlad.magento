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
        $categoryId = $category->getId(); // Получаем ID категории
        $options[] = ['value' => $categoryId, 'label' => $category->getName()];

        // Логируем ID категории для отладки
        error_log('Category ID: ' . $categoryId); // Записываем в лог
    }

    return $options;
}
}