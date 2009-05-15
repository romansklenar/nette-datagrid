DataGrid
--------
- DataGrid::$keyName
	 - Slouží pro určení klíče, nad kterým se budou provádět hromadné operace nebo akce.
	 - Např. pomocí checkboxů vyberu více záznamů, které chci smazat, formulář odešlu a v handleru vidím, nad kterýma hodnotama tohoto primárního klíče mám akci provést.

- tyto odesilaci tlacitka na hromadne operace: mely by mit uzivatelem definovan svuj handler v presenteru nebo v jine komponente


Filtry
------
Tridy filter jsou jen gettery FormControlu filtracniho pole, proto zde neni metoda pro 
aplikovani filtru a je v tride reprezentujici sloupec. Kdyz si nekdo bude chtit vytvorit novou tridu 
pro sloupec, muze to udelat vcetne aplikovani filtru aniz by musel rozsirovat i tridu filtru. 

