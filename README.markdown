#What is DataGrid

DataGrid control is a data bound list control that displays the items from datasource in a table. The DataGrid control allows you to select, sort, and manage these items.

**This is development version, it probably contains bugs and therefore it is NOT intended for production use.**

Keep in mind that things are **still in development**.

Feel free to suggest improvements.

##Basic usage:
It is required to provide mapping between DataGrid's column names and entity columns. This allows internal components (eg. sorting filtering) to work properly.

####Doctrine 2:

    $grid = new \DataGrid\DataGrid;

    //prepare datasource
    $dataSource = new \DataGrid\DataSources\Doctrine\QueryBuilder(
    		$em->createQueryBuilder() //$em instanceof Doctrine\ORM\EntityManager
    			->select('u.id, u.name, u.email, u.regTime, a.city, a.street') //columns to be used
    			->from('Models\User', 'u') //master table
    			->join('u.address', 'a') //joined table (one-to-one association)
    );

    //provide mapping betweeen DataGrid's column names and entity columns
    $dataSource->setMapping(array(
    	'id'		=> 'u.id',
    	'name'		=> 'u.name',
    	'email'		=> 'u.email',
    	'time'		=> 'u.regTime',
    	'city'		=> 'a.city',
    	'street'	=> 'a.street',
    ));

    //finally, set datasource to DataGrid
    $grid->setDataSource($dataSource);

    //now we're working with mapped fields
    $grid->addNumericColumn('id', 'ID')->addFilter();
    $grid->addColumn('name', 'Jméno')->addFilter();
    $grid->addColumn('email', 'Email')->addFilter();
    $grid->addColumn('city', 'Město')->addFilter();
    $grid->addColumn('street', 'Ulice')->addFilter();
    $grid->addDateColumn('time', 'Datum registrace')->addDateFilter();

####Dibi:

    TODO

####PHP Array:

    TODO

##Todo

####High priority:

 - Dibi wrapper (datasource)
 - Latte renderer

####Low priority:

- PHP Array wrapper (datasource)
- SelectBox formatting