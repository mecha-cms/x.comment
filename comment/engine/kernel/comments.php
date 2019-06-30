<?php

class Comments extends Pages {

    public function page(string $path) {
        return new Comment($path);
    }

}