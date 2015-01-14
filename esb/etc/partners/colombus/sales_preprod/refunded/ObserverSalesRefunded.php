<?php
/**
 *
 * @author tbondois
 */
class ObserverSalesRefunded extends ObserverSales
{
    public static $feed = 'refunded';

    public function onMapOut(EaiEvent $event)
    {
        static::_transco($event);
    }
}
