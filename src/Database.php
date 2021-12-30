<?php

namespace Sigwin\YASSG;

use Adbar\Dot;
use Symfony\Component\ExpressionLanguage\ExpressionLanguage;
use Symfony\Component\ExpressionLanguage\SyntaxError;

class Database
{
    private Dot $data;
    
    private ExpressionLanguage $expressionLanguage;

    public function __construct(array $data)
    {
        $this->data = dot($data);
        $this->expressionLanguage = new ExpressionLanguage();
    }
    
    public function query(string $query, ?string $condition = null): array
    {
        $keys = substr($query, -4) === '$key';
        if ($keys) {
            $query = substr($query, 0, strlen($query) - 5);
        }
        
        $result = $this->data->get($query);
        if ($result === null) {
            throw new \UnexpectedValueException('No matches');
        }
        if ($condition === null) {
            if ($keys) {
                return array_keys($result);
            }
            
            return $result;
        }

        $restricted = [];
        foreach ($result as $key => $item) {
            try {
                if ($this->expressionLanguage->evaluate($condition, $item)) {
                    if ($keys) {
                        $restricted[] = $key;
                    } else {
                        $restricted[$key] = $item;
                    }
                }
            } catch (SyntaxError $exception) {
                throw new \UnexpectedValueException(
                    sprintf('Syntax error while evaluating "%1$s": %2$s', $query.'.'.$key, $exception->getMessage()));
            }
        }

        return $restricted;
    }
}
