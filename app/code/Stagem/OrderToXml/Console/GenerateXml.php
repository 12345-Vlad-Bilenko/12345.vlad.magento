<?php
namespace Stagem\OrderToXml\Console;

use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Api\Data\OrderItemInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputOption;


class GenerateXml extends Command
{   const ORDER = 'order_id';

    protected function configure()
    {   $options = [
        new InputOption(
            self::ORDER,
            null,
            InputOption::VALUE_REQUIRED,
            'order_number'
        )
    ];

        $this->setName('ordertoxml:generate')
             ->setDescription('Generate XML for order')
             ->setDefinition ($options);
        parent::configure(); 
    }

    protected $orderRepository;
        public function __construct(
        OrderRepositoryInterface $orderRepository,)
        
        {   $this->orderRepository = $orderRepository;
            parent::__construct(); 
        }

    protected function execute(InputInterface $input, OutputInterface $output)
        {   $order_id = $input->getOption(self::ORDER);
        
            if ($order_id) 
            {   if (!ctype_digit($order_id) || strlen($order_id) !== 9) {
                $output->writeln("Ошибка: Номер заказа должен состоять из 9 цифр.");
                return 1; 
            }
            
            try 
            {   /** @var \Magento\Sales\Model\Order $order */
                $order = $this->orderRepository->get($order_id);

                $xml = new \SimpleXMLElement('<orders/>');
                $xml->addAttribute('place', 'magento');

                $orderXml = $xml->addChild('order');
                $orderXml->addAttribute('id', $order_id);
                $orderXml->addChild('date', $order->getCreatedAt());
                    if ($order->getShippingAddress()) {
                    $shippingAddress = $order->getShippingAddress();
                    $shippingDate = $shippingAddress->getShippingDate();
                    if ($shippingDate) {
                $orderXml->addChild('estimateDate', $shippingDate->format('Y-m-d'));}
                    else {
                $orderXml->addChild('estimateDate', "дата доставки не встановлена.");}}
                $orderXml->addChild('shipping', $order->getShippingAmount());
                $orderXml->addChild('discount', $order->getDiscountAmount());
                $orderXml->addChild('tax', $order->getTaxAmount());
                $orderXml->addChild('total', $order->getGrandTotal());

                $clientXml = $orderXml->addChild('client');
                $clientXml->addChild('firstName', $order->getCustomerFirstname());
                    if ($order->getCustomerMiddlename()){
                $clientXml->addChild('secondName', $order->getCustomerMiddlename());} 
                    else{
                $clientXml->addChild('secondName', "не зазначено");}
                $clientXml->addChild('lastName', $order->getCustomerLastname());
                $clientXml->addChild('phone', $order->getBillingAddress()->getTelephone());
                $clientXml->addChild('email', $order->getCustomerEmail());

/* не дороблено if ($order->getBillingAddress()) {
                    $billingAddress = $order->getBillingAddress();
                $orderXml->addChild('billingAddress', $billingAddress);
                    } else {
                $orderXml->addChild('billingAddress', "адреса не встановлена");}
                    if ($order->getShippingAddress()) {
                    $shippingAddress = $order->getShippingAddress();
                $orderXml->addChild('shippingAddress', $shippingAddress->format('text'));} 
                    else {
                $orderXml->addChild('shippingAddress', "адрес не встановлена");}
                    $shippingMethod = $order->getShippingMethod();
                $orderXml->addChild('delivery', $shippingMethod ?: "не обрано");
*/

                $itemsXml = $orderXml->addChild('items');
                /** @var OrderItemInterface $item */
                foreach ($order->getItems() as $item) {
                $itemXml = $itemsXml->addChild('item');
                $itemXml->addChild('sku', $item->getSku());
                $itemXml->addChild('count', $item->getQtyOrdered());
                $itemXml->addChild('price', $item->getPrice());
                $itemXml->addChild('discount', $item->getDiscountAmount());
                $itemXml->addChild('tax', $item->getTaxAmount());
                $itemXml->addChild('total', $item->getRowTotal());
                }
                
                date_default_timezone_set('Europe/Kiev'); 
                $timestamp = date('d-m-Y_H-i-s');
                $xml->asXML('C:/OSPanel/home/myproject/app/code/Stagem/OrderToXml/xmlFiles/order_' . $order_id . '_' . $timestamp . '.xml');


                $output->writeln("По замовленню з ID $order_id сформовано XML файл.");
            } 
            
            catch (\Magento\Framework\Exception\NoSuchEntityException $e)
            {   $output->writeln("Ошибка: Заказ с номером $order_id не найден.");
            }
        
            } 
            else 
            {   $output->writeln("Ошибка: Номер заказа не был введен.");
            }
        
        return 0;
        }
        
}
