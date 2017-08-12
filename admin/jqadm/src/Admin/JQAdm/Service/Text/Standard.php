<?php

/**
 * @license LGPLv3, http://opensource.org/licenses/LGPL-3.0
 * @copyright Aimeos (aimeos.org), 2017
 * @package Admin
 * @subpackage JQAdm
 */


namespace Aimeos\Admin\JQAdm\Service\Text;

sprintf( 'text' ); // for translation


/**
 * Default implementation of service text JQAdm client.
 *
 * @package Admin
 * @subpackage JQAdm
 */
class Standard
	extends \Aimeos\Admin\JQAdm\Common\Admin\Factory\Base
	implements \Aimeos\Admin\JQAdm\Common\Admin\Factory\Iface
{
	/** admin/jqadm/service/text/standard/subparts
	 * List of JQAdm sub-clients rendered within the service text section
	 *
	 * The output of the frontend is composed of the code generated by the JQAdm
	 * clients. Each JQAdm client can consist of serveral (or none) sub-clients
	 * that are responsible for rendering certain sub-parts of the output. The
	 * sub-clients can contain JQAdm clients themselves and therefore a
	 * hierarchical tree of JQAdm clients is composed. Each JQAdm client creates
	 * the output that is placed inside the container of its parent.
	 *
	 * At first, always the JQAdm code generated by the parent is printed, then
	 * the JQAdm code of its sub-clients. The order of the JQAdm sub-clients
	 * determines the order of the output of these sub-clients inside the parent
	 * container. If the configured list of clients is
	 *
	 *  array( "subclient1", "subclient2" )
	 *
	 * you can easily change the order of the output by reordering the subparts:
	 *
	 *  admin/jqadm/<clients>/subparts = array( "subclient1", "subclient2" )
	 *
	 * You can also remove one or more parts if they shouldn't be rendered:
	 *
	 *  admin/jqadm/<clients>/subparts = array( "subclient1" )
	 *
	 * As the clients only generates structural JQAdm, the layout defined via CSS
	 * should support adding, removing or reordering content by a fluid like
	 * design.
	 *
	 * @param array List of sub-client names
	 * @since 2017.07
	 * @category Developer
	 */
	private $subPartPath = 'admin/jqadm/service/text/standard/subparts';
	private $subPartNames = [];
	private $types;
	private $typelist = array( 'name', 'short', 'long' );


	/**
	 * Copies a resource
	 *
	 * @return string HTML output
	 */
	public function copy()
	{
		$view = $this->addViewData( $this->getView() );

		$view->textData = $this->toArray( $view->item, true );
		$view->textBody = '';

		foreach( $this->getSubClients() as $client ) {
			$view->textBody .= $client->copy();
		}

		return $this->render( $view );
	}


	/**
	 * Creates a new resource
	 *
	 * @return string HTML output
	 */
	public function create()
	{
		$view = $this->addViewData( $this->getView() );

		$view->textData = $view->param( 'text', [] );
		$view->textBody = '';

		foreach( $this->getSubClients() as $client ) {
			$view->textBody .= $client->create();
		}

		return $this->render( $view );
	}


	/**
	 * Returns a single resource
	 *
	 * @return string HTML output
	 */
	public function get()
	{
		$view = $this->addViewData( $this->getView() );

		$view->textData = $this->toArray( $view->item );
		$view->textBody = '';

		foreach( $this->getSubClients() as $client ) {
			$view->textBody .= $client->get();
		}

		return $this->render( $view );
	}


	/**
	 * Saves the data
	 */
	public function save()
	{
		$view = $this->getView();
		$context = $this->getContext();

		$manager = \Aimeos\MShop\Factory::createManager( $context, 'service/lists' );
		$textManager = \Aimeos\MShop\Factory::createManager( $context, 'text' );

		$manager->begin();
		$textManager->begin();

		try
		{
			$this->fromArray( $view->item, $view->param( 'text', [] ) );
			$view->textBody = '';

			foreach( $this->getSubClients() as $client ) {
				$view->textBody .= $client->save();
			}

			$textManager->commit();
			$manager->commit();
			return;
		}
		catch( \Aimeos\MShop\Exception $e )
		{
			$error = array( 'service-item-text' => $context->getI18n()->dt( 'mshop', $e->getMessage() ) );
			$view->errors = $view->get( 'errors', [] ) + $error;
		}
		catch( \Exception $e )
		{
			$error = array( 'service-item-text' => $e->getMessage() . ', ' . $e->getFile() . ':' . $e->getLine() );
			$view->errors = $view->get( 'errors', [] ) + $error;
		}

		$textManager->rollback();
		$manager->rollback();

		throw new \Aimeos\Admin\JQAdm\Exception();
	}


	/**
	 * Returns the sub-client given by its name.
	 *
	 * @param string $type Name of the client type
	 * @param string|null $name Name of the sub-client (Default if null)
	 * @return \Aimeos\Admin\JQAdm\Iface Sub-client object
	 */
	public function getSubClient( $type, $name = null )
	{
		/** admin/jqadm/service/text/decorators/excludes
		 * Excludes decorators added by the "common" option from the service JQAdm client
		 *
		 * Decorators extend the functionality of a class by adding new aspects
		 * (e.g. log what is currently done), executing the methods of the underlying
		 * class only in certain conditions (e.g. only for logged in users) or
		 * modify what is returned to the caller.
		 *
		 * This option allows you to remove a decorator added via
		 * "admin/jqadm/common/decorators/default" before they are wrapped
		 * around the JQAdm client.
		 *
		 *  admin/jqadm/service/text/decorators/excludes = array( 'decorator1' )
		 *
		 * This would remove the decorator named "decorator1" from the list of
		 * common decorators ("\Aimeos\Admin\JQAdm\Common\Decorator\*") added via
		 * "admin/jqadm/common/decorators/default" to the JQAdm client.
		 *
		 * @param array List of decorator names
		 * @since 2017.07
		 * @category Developer
		 * @see admin/jqadm/common/decorators/default
		 * @see admin/jqadm/service/text/decorators/global
		 * @see admin/jqadm/service/text/decorators/local
		 */

		/** admin/jqadm/service/text/decorators/global
		 * Adds a list of globally available decorators only to the service JQAdm client
		 *
		 * Decorators extend the functionality of a class by adding new aspects
		 * (e.g. log what is currently done), executing the methods of the underlying
		 * class only in certain conditions (e.g. only for logged in users) or
		 * modify what is returned to the caller.
		 *
		 * This option allows you to wrap global decorators
		 * ("\Aimeos\Admin\JQAdm\Common\Decorator\*") around the JQAdm client.
		 *
		 *  admin/jqadm/service/text/decorators/global = array( 'decorator1' )
		 *
		 * This would add the decorator named "decorator1" defined by
		 * "\Aimeos\Admin\JQAdm\Common\Decorator\Decorator1" only to the JQAdm client.
		 *
		 * @param array List of decorator names
		 * @since 2017.07
		 * @category Developer
		 * @see admin/jqadm/common/decorators/default
		 * @see admin/jqadm/service/text/decorators/excludes
		 * @see admin/jqadm/service/text/decorators/local
		 */

		/** admin/jqadm/service/text/decorators/local
		 * Adds a list of local decorators only to the service JQAdm client
		 *
		 * Decorators extend the functionality of a class by adding new aspects
		 * (e.g. log what is currently done), executing the methods of the underlying
		 * class only in certain conditions (e.g. only for logged in users) or
		 * modify what is returned to the caller.
		 *
		 * This option allows you to wrap local decorators
		 * ("\Aimeos\Admin\JQAdm\Service\Decorator\*") around the JQAdm client.
		 *
		 *  admin/jqadm/service/text/decorators/local = array( 'decorator2' )
		 *
		 * This would add the decorator named "decorator2" defined by
		 * "\Aimeos\Admin\JQAdm\Service\Decorator\Decorator2" only to the JQAdm client.
		 *
		 * @param array List of decorator names
		 * @since 2017.07
		 * @category Developer
		 * @see admin/jqadm/common/decorators/default
		 * @see admin/jqadm/service/text/decorators/excludes
		 * @see admin/jqadm/service/text/decorators/global
		 */
		return $this->createSubClient( 'service/text/' . $type, $name );
	}


	/**
	 * Adds the required data used in the stock template
	 *
	 * @param \Aimeos\MW\View\Iface $view View object
	 * @return \Aimeos\MW\View\Iface View object with assigned parameters
	 */
	protected function addViewData( \Aimeos\MW\View\Iface $view )
	{
		if( $view->get( 'pageLanguages', [] ) === [] ) {
			throw new \Aimeos\Admin\JQAdm\Exception( 'No languages available. Please enable at least one language' );
		}

		$manager = \Aimeos\MShop\Factory::createManager( $this->getContext(), 'text/type' );
		$view->textTypes = $manager->searchItems( $manager->createSearch() );

		return $view;
	}


	/**
	 * Returns the list of sub-client names configured for the client.
	 *
	 * @return array List of JQAdm client names
	 */
	protected function getSubClientNames()
	{
		return $this->getContext()->getConfig()->get( $this->subPartPath, $this->subPartNames );
	}


	/**
	 * Returns the ID for the given text type
	 *
	 * @param string $type Text type
	 * @return integer Type ID for the given type
	 * @throws \Aimeos\Admin\JQAdm\Exception If the given type is unknown
	 */
	protected function getTypeId( $type )
	{
		if( $this->types === null )
		{
			$this->types = [];
			$manager = \Aimeos\MShop\Factory::createManager( $this->getContext(), 'text/type' );

			$search = $manager->createSearch();
			$search->setConditions( $search->compare( '==', 'text.type.domain', 'service' ) );

			foreach( $manager->searchItems( $search ) as $id => $typeItem ) {
				$this->types[$typeItem->getCode()] = $id;
			}
		}

		if( isset( $this->types[$type] ) ) {
			return $this->types[$type];
		}

		throw new \Aimeos\Admin\JQAdm\Exception( sprintf( 'Unknown type "%1$s"', $type ) );
	}


	/**
	 * Returns the text types that are managed by this subpart
	 *
	 * @return array List of text type codes
	 */
	protected function getTypes()
	{
		/** admin/jqadm/service/text/standard/types
		 * List of text types that are managed by the service text subpart
		 *
		 * To extend or reduce the text types that can be managed by the service
		 * text subpart, you can modify this configuration setting and add more
		 * text types or remove existing ones.
		 *
		 * '''Note:''' You have to overwrite the corresponding template as well
		 * to add or remove the corresponding input fields for the new text type
		 * list.
		 *
		 * @param array List of text type codes
		 * @since 2016.11
		 * @category Developer
		 */
		return $this->getContext()->getConfig()->get( 'admin/jqadm/service/text/standard/types', $this->typelist );
	}


	/**
	 * Creates new and updates existing items using the data array
	 *
	 * @param \Aimeos\MShop\Service\Item\Iface $item Service item object without referenced domain items
	 * @param string[] $data Data array
	 */
	protected function fromArray( \Aimeos\MShop\Service\Item\Iface $item, array $data )
	{
		$listIds = [];
		$id = $item->getId();
		$context = $this->getContext();

		$manager = \Aimeos\MShop\Factory::createManager( $context, 'service' );
		$textManager = \Aimeos\MShop\Factory::createManager( $context, 'text' );
		$listManager = \Aimeos\MShop\Factory::createManager( $context, 'service/lists' );
		$listTypeManager = \Aimeos\MShop\Factory::createManager( $context, 'service/lists/type' );

		$listItems = $manager->getItem( $id, array( 'text' ) )->getListItems( 'text', 'default' );
		$langIds = (array) $this->getValue( $data, 'langid', [] );


		$listItem = $listManager->createItem();
		$listItem->setTypeId( $listTypeManager->findItem( 'default', [], 'text' )->getId() );
		$listItem->setDomain( 'text' );
		$listItem->setParentId( $id );
		$listItem->setStatus( 1 );

		$newItem = $textManager->createItem();
		$newItem->setDomain( 'service' );
		$newItem->setStatus( 1 );


		foreach( $langIds as $idx => $langid )
		{
			foreach( $this->getTypes() as $type )
			{
				if( ( $content = trim( $this->getValue( $data, $type . '/content/' . $idx, '' ) ) ) === '' ) {
					continue;
				}

				$listid = $this->getValue( $data, $type . '/listid/' . $idx );
				$listIds[] = $listid;

				if( !isset( $listItems[$listid] ) )
				{
					$textItem = clone $newItem;

					$litem = $listItem;
					$litem->setId( null );
				}
				else
				{
					$litem = $listItems[$listid];
					$textItem = $litem->getRefItem();
				}

				$textItem->setContent( $content );
				$textItem->setLabel( mb_strcut( $textItem->getContent(), 0, 255 ) );
				$textItem->setTypeId( $this->getTypeId( $type ) );
				$textItem->setLanguageId( $langid );

				$textItem = $textManager->saveItem( $textItem );

				$litem->setPosition( $idx );
				$litem->setRefId( $textItem->getId() );

				$listManager->saveItem( $litem, false );
			}
		}


		$rmIds = $allListIds = [];

		foreach( $listItems as $id => $listItem )
		{
			if( in_array( $listItem->getRefItem()->getType(), $this->getTypes() ) ) {
				$allListIds[] = $id;
			}
		}

		$rmListIds = array_diff( $allListIds, $listIds );

		foreach( $rmListIds as $id ) {
			$rmIds[] = $listItems[$id]->getRefId();
		}

		$listManager->deleteItems( $rmListIds  );
		$textManager->deleteItems( $rmIds  );
	}


	/**
	 * Constructs the data array for the view from the given item
	 *
	 * @param \Aimeos\MShop\Service\Item\Iface $item Service item object including referenced domain items
	 * @param boolean $copy True if items should be copied, false if not
	 * @return string[] Multi-dimensional associative list of item data
	 */
	protected function toArray( \Aimeos\MShop\Service\Item\Iface $item, $copy = false )
	{
		$data = [];

		foreach( $item->getListItems( 'text', 'default' ) as $listItem )
		{
			if( ( $refItem = $listItem->getRefItem() ) === null ) {
				continue;
			}

			$type = $refItem->getType();
			$langid = $refItem->getLanguageId();
			$data['langid'][$langid] = $langid;
			$data['siteid'][$langid] = $item->getSiteId();

			if( in_array( $type, $this->getTypes() ) )
			{
				$data[$type]['listid'][$langid] = $listItem->getId();
				$data[$type]['content'][$langid] = $refItem->getContent();
			}
		}

		return $data;
	}


	/**
	 * Returns the rendered template including the view data
	 *
	 * @param \Aimeos\MW\View\Iface $view View object with data assigned
	 * @return string HTML output
	 */
	protected function render( \Aimeos\MW\View\Iface $view )
	{
		/** admin/jqadm/service/text/template-item
		 * Relative path to the HTML body template of the text subpart for services.
		 *
		 * The template file contains the HTML code and processing instructions
		 * to generate the result shown in the body of the frontend. The
		 * configuration string is the path to the template file relative
		 * to the templates directory (usually in admin/jqadm/templates).
		 *
		 * You can overwrite the template file configuration in extensions and
		 * provide alternative templates. These alternative templates should be
		 * named like the default one but with the string "default" replaced by
		 * an unique name. You may use the name of your project for this. If
		 * you've implemented an alternative client class as well, "default"
		 * should be replaced by the name of the new class.
		 *
		 * @param string Relative path to the template creating the HTML code
		 * @since 2016.04
		 * @category Developer
		 */
		$tplconf = 'admin/jqadm/service/text/template-item';
		$default = 'service/item-text-default.php';

		return $view->render( $view->config( $tplconf, $default ) );
	}
}
