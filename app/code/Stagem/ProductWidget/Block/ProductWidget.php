<?php

namespace Stagem\ProductWidget\Block;

use Magento\Framework\View\Element\Template;
use Magento\Widget\Block\BlockInterface;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Magento\Catalog\Model\CategoryRepository;

class ProductWidget extends Template implements BlockInterface
{
    protected $productCollectionFactory;
    protected $categoryRepository;

    public function getCategoryId()
    {
        return (int)$this->getData('catid');
    }

    public function getProductCount()
    {
        return (int)$this->getData('product_count');
    }

    public function __construct(
        Template\Context $context,
        CollectionFactory $productCollectionFactory,
        CategoryRepository $categoryRepository,
        array $data = []
    ) {
        $this->productCollectionFactory = $productCollectionFactory;
        $this->categoryRepository = $categoryRepository;
        parent::__construct($context, $data);
        $this->setTemplate('Stagem_ProductWidget::productwidget.phtml');
    }

    public function getProductCollection()
    {
        $collection = $this->productCollectionFactory->create();
        
        $categoryId = $this->getCategoryId();

        try {
            $category = $this->categoryRepository->get($categoryId);
            $collection->addAttributeToSelect('*')
                       ->addCategoryFilter($category)
                       ->setPageSize($this->getProductCount())
                       ->setOrder('price', 'DESC');
        } catch (\Magento\Framework\Exception\NoSuchEntityException $e) {
            return null; // Возвращаем null, если категория не найдена
        }

        return $collection;
    }
}