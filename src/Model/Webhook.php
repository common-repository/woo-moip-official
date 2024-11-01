<?php
namespace Woocommerce\Moip\Model;

if ( ! function_exists( 'add_action' ) ) {
	exit( 0 );
}

//Moip SDK
use Requests;

use Woocommerce\Moip\Core;

class Webhook extends Resource
{
    const PATH_NOTIFICATIONS = 'preferences/notifications';
    const PATH_CONSULT       = 'webhooks';

    public function create( $args = [] )
    {
    	$defaults = [
			'media'  => 'WEBHOOK',
			'target' => Core::get_webhook_url(),
			'events' => [
				//'ORDER.*',
				'PAYMENT.*',
            ],
        ];

        $response = $this->httpRequest(
            $this->get_endpoint( self::PATH_NOTIFICATIONS ),
            Requests::POST,
            array_merge( $defaults, $args )
        );

        $this->populate( (object) $response );

        return $this->data;
    }

    public function delete( $notification_id )
    {
        $defaults = [
            'target' => Core::get_webhook_url(),
        ];

        $path = sprintf( '%s/%s', self::PATH_NOTIFICATIONS, $notification_id );
        $response = $this->httpRequest(
            $this->get_endpoint( $path ),
            Requests::DELETE,
            array_merge($defaults)
        );

        $this->populate( (object) $response );

        return $this->data;
    }

    public function get( $notification_id = '' )
    {
        if ( ! empty( $notification_id ) ) {
            $notification_id = sprintf( '/%s', $notification_id );
        }

        $response = $this->httpRequest(
            $this->get_endpoint( self::PATH_NOTIFICATIONS . $notification_id ),
            Requests::GET
        );

        $this->populate( (object) $response );

        return $this->data;
    }

    public function get_sent( $resource_id = '' )
    {
        if ( ! empty( $resource_id ) ) {
            $resource_id = sprintf( '?resourceId=%s', $resource_id );
        }

        $response = $this->httpRequest(
            $this->get_endpoint( self::PATH_CONSULT . $resource_id ),
            Requests::GET
        );

        $this->populate( (object) $response );

        return $this->data;
    }

    public function sent_notification( $args = array() )
    {
        if ( ! isset( $args['resourceId'] ) ) {
            return false;
        }

        $defaults = [
            'resourceId' => null,
            'event'      => 'ORDER.CREATED',
        ];

        $response = $this->httpRequest(
            $this->get_endpoint( self::PATH_CONSULT ),
            Requests::POST,
            array_merge( $defaults, $args )
        );

        $this->populate( (object) $response );

        return $this->data;
    }
}
