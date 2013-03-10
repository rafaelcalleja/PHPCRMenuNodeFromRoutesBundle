<?php
namespace RC\PHPCRMenuNodeFromRoutesBundle\Events;

final class RouteEvents{
	/**
	 * El evento «rc.route.added» es lanzado cada vez que se crea una ruta phpcr
	 * en el sistema.
	 *
	 * El escucha del evento recibe una
	 * instancia de RC\PHPCRMenuNodeFromRoutesBundle\Events\RouteDataEvent.
	 *
	 * @var string
	 */
	const ROUTE_ADDED = 'rc.route.added';
}