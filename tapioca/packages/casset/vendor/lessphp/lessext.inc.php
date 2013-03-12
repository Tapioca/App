<?php

class lessExt extends lessc 
{
	protected function tryImport($importPath, $parentBlock, $out) {
		if ($importPath[0] == "function" && $importPath[1] == "url") {
			$importPath = $this->flattenList($importPath[2]);
		}

		$str = $this->coerceString($importPath);
		if ($str === null) return false;

		$url = $this->compileValue($this->lib_e($str));

		// don't import if it ends in css
		if (substr_compare($url, '.css', -4, 4) === 0) return false;

		$realPath = $this->findImport($url);
		if ($realPath === null) return false;

		if ($this->importDisabled) {
			return array(false, "/* import disabled */");
		}

		// don't import if it ends in css
		if (substr_compare($url, '.php', -4, 4) === 0)
		{
			$fileContent = \View::forge( $realPath )->auto_filter(false)->render();
		} 
		else
		{
			$fileContent = file_get_contents( $realPath );
		}

		$this->addParsedFile($realPath);
		$parser = $this->makeParser($realPath);
		$root = $parser->parse( $fileContent );

		// set the parents of all the block props
		foreach ($root->props as $prop) {
			if ($prop[0] == "block") {
				$prop[1]->parent = $parentBlock;
			}
		}

		// copy mixins into scope, set their parents
		// bring blocks from import into current block
		// TODO: need to mark the source parser	these came from this file
		foreach ($root->children as $childName => $child) {
			if (isset($parentBlock->children[$childName])) {
				$parentBlock->children[$childName] = array_merge(
					$parentBlock->children[$childName],
					$child);
			} else {
				$parentBlock->children[$childName] = $child;
			}
		}

		$pi = pathinfo($realPath);
		$dir = $pi["dirname"];

		list($top, $bottom) = $this->sortProps($root->props, true);
		$this->compileImportedProps($top, $parentBlock, $out, $parser, $dir);

		return array(true, $bottom, $parser, $dir);
	}

}