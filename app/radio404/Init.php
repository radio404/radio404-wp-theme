<?php

namespace radio404\Init;


use radio404\PostType;
use radio404\Core;

class App {

	public function __construct()
	{
		new Core\ThemeSetup();
		new Core\Security();
		new Core\AdminPages();
		new Core\ApiLoader();
		new Core\Acf();

		new PostType\Schedule();
		new PostType\Track();
		new PostType\Album();
		new PostType\Artist();
		new PostType\Label();
		new PostType\Podcast();
	}

}