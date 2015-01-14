<?php
/**
 *
 * @author tbondois
 */
class ObserverSalesInvoiced extends ObserverSales
{
    public static $feed = 'invoiced';//'invoiced';//'ordered'

    public static function onMapOut(EaiEvent $event)
    {
        static::_transco($event);
    }
}
