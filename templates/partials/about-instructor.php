<?php
use Mosaicpro\Media\Media;

echo Media::make()
    ->addObjectLeft(get_avatar( get_the_author(), 64 ))
    ->addBody('Instructor', get_the_author());