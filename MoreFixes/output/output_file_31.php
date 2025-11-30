                    $params[":id_$idx"] = new Parameter("id_$idx", $id, $idType);
                    ++$idx;
                }

                $queryBuilder = $repository->createQueryBuilder('o');

                if ($params) {
                    $queryBuilder
                        ->where(sprintf("o.$idField IN (%s)", implode(', ', array_keys($params))))
                        ->setParameters(new ArrayCollection($params));
                }

                $options['choices'] = $queryBuilder->getQuery()->getResult();
            } else {
                $options['choices'] = $repository->createQueryBuilder('o')
                    ->where("o.$idField = :id")
                    ->setParameter('id', $data['autocomplete'], $idType)
                    ->getQuery()
                    ->getResult();
            }
        }

        // reset some critical lazy options
        unset($options['em'], $options['loader'], $options['empty_data'], $options['choice_list'], $options['choices_as_values']);

        $form->add('autocomplete', EntityType::class, $options);
    }
