<?php
/***
 *
 * This file is part of Qc Redirects project.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 *  (c) 2022 <techno@quebec.ca>
 *
 ***/

namespace QcRedirects\Mapper;

use QcRedirects\Domaine\Model\RedirectEntity;

class RedirectEntityMapper
{
    /**
     * This function returns the RedirectEntity after mapping from inserted row by the user
     * @param $row
     * @return RedirectEntity
     */
    public function rowToRedirectEntity($row) : RedirectEntity{
        $redirectEntity = new RedirectEntity();
        $redirectEntity->setTitle($row[0]);
        $redirectEntity->setSourceHost($row[1]);
        $redirectEntity->setSourcePath($row[2]);
        $redirectEntity->setTarget($row[3]);
        $redirectEntity->setStartTime($row[4]);
        $redirectEntity->setEndTime($row[5]);
        $redirectEntity->setIsRegExp($row[6]);
        $redirectEntity->setStatusCode((int)$row[7]);
        return $redirectEntity;
    }

    /**
     * This function is used for mapping a redirectEntity to an array for DatabaseHandler
     * @param RedirectEntity $entity
     * @return array
     */
    public function redirectEntityToDBRow(RedirectEntity $entity): array
    {
        return
            [
                'pid' => '0',
                'title' => $entity->getTitle(),
                'source_host' => $entity->getSourceHost(),
                'source_path' => $entity->getSourcePath(),
                'target' => $entity->getTarget(),
                'starttime' => strtotime($entity->getStartTime()),
                'endtime' => strtotime($entity->getEndTime()),
                'is_regexp' => strtolower($entity->getIsRegExp()) == 'true' ? 1 : 0,
                'target_statuscode' => (int)$entity->getStatusCode(),
             ];
    }
}