<?php
/************************************************************************
 * This file is part of EspoCRM.
 *
 * EspoCRM – Open Source CRM application.
 * Copyright (C) 2014-2024 Yurii Kuznietsov, Taras Machyshyn, Oleksii Avramenko
 * Website: https://www.espocrm.com
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program. If not, see <https://www.gnu.org/licenses/>.
 *
 * The interactive user interfaces in modified source and object code versions
 * of this program must display Appropriate Legal Notices, as required under
 * Section 5 of the GNU Affero General Public License version 3.
 *
 * In accordance with Section 7(b) of the GNU Affero General Public License version 3,
 * these Appropriate Legal Notices must retain the display of the "EspoCRM" word.
 ************************************************************************/

namespace Espo\Classes\Select\Email\Where\ItemConverters;

use Espo\Core\Select\Where\Item;
use Espo\Core\Select\Where\ItemConverter;
use Espo\Classes\Select\Email\Helpers\EmailAddressHelper;
use Espo\ORM\Name\Attribute;
use Espo\ORM\Query\Part\Condition as Cond;
use Espo\ORM\Query\Part\Expression as Expr;
use Espo\ORM\Query\Part\WhereClause;
use Espo\ORM\Query\Part\WhereItem as WhereClauseItem;
use Espo\ORM\Query\SelectBuilder;
use Espo\ORM\Query\SelectBuilder as QueryBuilder;

class ToEquals implements ItemConverter
{
    protected string $addressType = 'to';

    public function __construct(
        private EmailAddressHelper $emailAddressHelper,
    ) {}

    public function convert(QueryBuilder $queryBuilder, Item $item): WhereClauseItem
    {
        $value = $item->getValue();

        if (!$value) {
            return WhereClause::fromRaw([Attribute::ID => null]);
        }

        $emailAddressId = $this->emailAddressHelper->getEmailAddressIdByValue($value);

        if (!$emailAddressId) {
            return WhereClause::fromRaw([Attribute::ID => null]);
        }

        return Cond::exists(
            SelectBuilder::create()
                ->from('EmailEmailAddress', 'sq')
                ->where([
                    'emailAddressId' => $emailAddressId,
                    'addressType' => $this->addressType,
                ])
                ->where(
                    Cond::equal(
                        Expr::column('sq.emailId'),
                        Expr::column('email.id')
                    )
                )
                ->build()
        );
    }
}
