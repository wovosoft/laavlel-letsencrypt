<?php

namespace Wovosoft\LaravelTypescript;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Spatie\LaravelIgnition\Support\Composer\ComposerClassMap;

class ModelFinder
{
    public function getAllModels(): array
    {
        $namespaces = array_keys((new ComposerClassMap)->listClasses());
        return array_filter($namespaces, function ($class) {
            return is_subclass_of($class, Model::class);
        });
    }

    public function getModelsIn(string $directory): Collection
    {
        return collect(\File::allFiles($directory))
            ->map(function (\SplFileInfo $info) {
                $fileInfo = $this->parseFile($info->getRealPath());
                $models = [];
                foreach ($fileInfo['class'] as $class) {
                    $class = $fileInfo['namespace'] . "\\" . $class;
                    if (is_subclass_of($class, Model::class)) {
                        $models[] = $class;
                    }
                }
                return $models;
            })
            ->filter(fn($item) => !empty($item))
            ->flatten();
    }

    /**
     * @link https://forums.phpfreaks.com/topic/313493-get-class-name-by-filename/
     * @param string $filename
     * @return array
     * @throws \Exception
     */
    private function parseFile(string $filename): array
    {
        $getNext = null;
        $getNextNamespace = false;
        $skipNext = false;
        $isAbstract = false;
        $rs = ['namespace' => null, 'class' => [], 'trait' => [], 'interface' => [], 'abstract' => []];
        foreach (\PhpToken::tokenize(file_get_contents($filename)) as $token) {
            if (!$token->isIgnorable()) {
                $name = $token->getTokenName();
                switch ($name) {
                    case 'T_NAMESPACE':
                        $getNextNamespace = true;
                        break;
                    case 'T_EXTENDS':
                    case 'T_USE':
                    case 'T_IMPLEMENTS':
                        //case 'T_ATTRIBUTE':
                        $skipNext = true;
                        break;
                    case 'T_ABSTRACT':
                        $isAbstract = true;
                        break;
                    case 'T_CLASS':
                    case 'T_TRAIT':
                    case 'T_INTERFACE':
                        if ($skipNext) {
                            $skipNext = false;
                        } else {
                            $getNext = strtolower(substr($name, 2));
                        }
                        break;
                    case 'T_NAME_QUALIFIED':
                    case 'T_STRING':
                        if ($getNextNamespace) {
                            if (array_filter($rs)) {
                                throw new \Exception(sprintf('Namespace must be defined first in %s', $filename));
                            } else {
                                $rs['namespace'] = $token->text;
                            }
                            $getNextNamespace = false;
                        } elseif ($skipNext) {
                            $skipNext = false;
                        } elseif ($getNext) {
                            if (in_array($token->text, $rs[$getNext])) {
                                throw new \Exception(sprintf('%s %s has already been found in %s', $rs[$getNext], $token->text, $filename));
                            }
                            if ($isAbstract) {
                                $isAbstract = false;
                                $getNext = 'abstract';
                            }
                            $rs[$getNext][] = $token->text;
                            $getNext = null;
                        }
                        break;
                    default:
                        $getNext = null;
                }
            }
        }
        $rs['filename'] = $filename;
        return $rs;
    }
}
