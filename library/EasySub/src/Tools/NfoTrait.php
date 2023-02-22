<?php

namespace EasySub\Tools;

use SimpleXMLElement;

trait NfoTrait
{
    protected string $message;

    /**
     * 获取NFO信息
     * @param string $dirPath
     * @param string $fileName
     * @param string $nfoType movieInfo,tvInfo,seasonInfo,episodeInfo
     * @return array
     */
    protected function getNfo(string $dirPath,string $fileName,string $nfoType = 'movieInfo'): array
    {
        if (is_readable($dirPath . '/' . $fileName . '.nfo')) {
            //EMBY 刮削信息
            $fileInfo = simplexml_load_string(file_get_contents($dirPath . '/' . $fileName . '.nfo'));
            return $this->getDataFromEmbyNfo($fileInfo,$nfoType);
        }

        return [];
    }

    /**
     * 获取NFO数据到数组
     * @param SimpleXMLElement $simpleXml
     * @param string $nfoType movieInfo,tvInfo,seasonInfo,episodeInfo
     * @return array
     */
    private function getDataFromEmbyNfo(SimpleXMLElement $simpleXml, string $nfoType = 'movieInfo'): array
    {
        switch ($nfoType) {
            case 'movieInfo':
                $returnField = [
                    'title',
                    'originaltitle',
                    'dateadded',
                    'rating',
                    'year',
                    'imdbid',
                    'tmdbid',
                    'runtime'
                ];
                break;
            case 'tvInfo':
                $returnField = [
                    'title',
                    'dateadded',
                    'originaltitle',
                    'rating',
                    'year',
                    'imdb_id',
                    'tmdbid',
                    'tvdbid'
                ];
                break;
            case 'seasonInfo':
                $returnField = [
                    'title',
                    'year',
                    'tvdbid',
                    'releasedate',
                    'seasonnumber'
                ];
                break;
            case 'episodeInfo':
                $returnField = [
                    'title',
                    'rating',
                    'year',
                    'imdbid',
                    'tvdbid',
                    'sonarrid',
                    'episode',
                    'season',
                ];
                break;
            default:
                return [];
        }

        $data = [];
        foreach ($returnField as $key) {
            switch ($key) {
                case 'originaltitle':
                    if (isset($simpleXml->{$key})) {
                        $data['original_title'] = $simpleXml->{$key};
                    }
                    break;
                case 'dateadded':
                    if (isset($simpleXml->{$key})) {
                        $data['date_added'] = $simpleXml->{$key};
                    }
                    break;
                case 'imdbid':
                case 'imdb_id':
                    if (isset($simpleXml->{$key})) {
                        $data['imdb_id'] = $simpleXml->{$key};
                    }
                    break;
                case 'tmdbid':
                    if (isset($simpleXml->{$key})) {
                        $data['tmdb_id'] = $simpleXml->{$key};
                    }
                    break;
                case 'tvdbid':
                    if (isset($simpleXml->{$key})) {
                        $data['tvdb_id'] = $simpleXml->{$key};
                    }
                    break;
                case 'releasedate':
                    if (isset($simpleXml->{$key})) {
                        $data['release_date'] = $simpleXml->{$key};
                    }
                    break;
                case 'seasonnumber':
                    if (isset($simpleXml->{$key})) {
                        $data['season_number'] = $simpleXml->{$key};
                    }
                    break;
                case 'sonarrid':
                    if (isset($simpleXml->{$key})) {
                        $data['sonarr_id'] = $simpleXml->{$key};
                    }
                    break;
                default:
                    if (isset($simpleXml->{$key})) {
                        $data[$key] = $simpleXml->{$key};
                    }
                    break;
            }
        }
        return $data;
    }

    public function getMessage(): string
    {
        return $this->message;
    }
}