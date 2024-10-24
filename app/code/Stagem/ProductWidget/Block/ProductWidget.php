<?php

namespace Stagem\ProductWidget\Block;

use Magento\Framework\View\Element\Template;
use Magento\Widget\Block\BlockInterface;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Magento\Catalog\Model\CategoryRepository;
use Magento\Catalog\Helper\Image as ImageHelper;

class ProductWidget extends Template implements BlockInterface
{
    protected CollectionFactory $productCollectionFactory;
    protected CategoryRepository $categoryRepository;
    protected ImageHelper $imageHelper;

    public function __construct(
        Template\Context $context,
        CollectionFactory $productCollectionFactory,
        CategoryRepository $categoryRepository,
        ImageHelper $imageHelper,
        array $data = []
    ) {
        $this->productCollectionFactory = $productCollectionFactory;
        $this->categoryRepository = $categoryRepository;
        $this->imageHelper = $imageHelper;

        parent::__construct($context, $data);
        $this->setTemplate('Stagem_ProductWidget::productwidget.phtml');
    }

    public function getCategoryId(): int
    {
        return (int)$this->getData('catid');
    }

    public function getProductCount(): int
    {
        $productCount = (int)$this->getData('product_count');

        if ($productCount < 1) {
            $productCount = 1;
        }
        if ($productCount > 40) {
            $productCount = 40;
        }

        return $productCount;
    }

    public function getProductCollection(): array
    {
        $collection = $this->productCollectionFactory->create();

        try {
            $categoryId = $this->getCategoryId();
            $category = $this->categoryRepository->get($categoryId);

            $collection->addAttributeToSelect('*')
                       ->addCategoryFilter($category)
                       ->setPageSize($this->getProductCount())
                       ->setOrder('price', 'DESC');

            foreach ($collection as $product) {
                $imageUrl = $this->imageHelper->init($product, 'product_base_image')->getUrl();
                $product->setData('image_url', $imageUrl);
            }

            return $collection->getItems();
        } catch (\Magento\Framework\Exception\NoSuchEntityException $e) {
            return [];
        }
    }
}