<?php

namespace EasySub\Video;

class Store
{
    /**
     * 电影库路径
     * @var array|string[]
     */
    private array $movieLibrary = [
        '/data/movies-1',
        '/data/movies-2',
        '/data/movies-3'
    ];

    /**
     * 剧集库路径
     * @var array|string[]
     */
    private array $tvLibrary = [
        '/data/tv-1',
        '/data/tv-2',
        '/data/tv-3'
    ];

    /**
     * 获取有效电影库路径
     * @return array
     */
    public function getMovieLibrary(): array
    {
        $returnArray = [];
        foreach ($this->movieLibrary as $moviePath) {
            if (is_readable($moviePath) || is_dir($moviePath)) {
                $returnArray[] = $moviePath;
            }
        }
        return $returnArray;
    }

    /**
     * 增加电影库路径
     * @param string $moviePath
     * @return bool
     */
    public function addMovieLibrary(string $moviePath): bool
    {
        if (is_dir($moviePath)) {
            $this->movieLibrary[] = $moviePath;
            return true;
        }
        return false;
    }

    /**
     * 获取有效剧集库路径
     * @return array
     */
    public function getTvLibrary(): array
    {
        $returnArray = [];
        foreach ($this->tvLibrary as $tvPath) {
            if (is_readable($tvPath) || is_dir($tvPath)) {
                $returnArray[] = $tvPath;
            }
        }
        return $returnArray;
    }

    /**
     * 增加剧集库路径
     * @param string $tvPath
     * @return bool
     */
    public function addTvLibrary(string $tvPath): bool
    {
        if (is_dir($tvPath)) {
            $this->tvLibrary[] = $tvPath;
            return true;
        }
        return false;
    }
}