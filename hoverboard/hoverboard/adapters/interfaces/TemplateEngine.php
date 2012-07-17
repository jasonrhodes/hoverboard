<?php

namespace hoverboard\adapters\interfaces;

interface TemplateEngine
{
	public function render($path, $data);
}