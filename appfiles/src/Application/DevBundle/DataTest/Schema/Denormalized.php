<?php

namespace Application\DevBundle\DataTest\Schema;

class Denormalized extends AbstractSchema
{
		public function testCategory()
	{
		$sql = "
			SELECT COUNT(*)
			FROM tickets
			WHERE
				category_id IN (1, 4)
		";
		return $sql;
	}

	public function testCategoryDepartment()
	{
		$sql = "
			SELECT COUNT(*)
			FROM tickets
			WHERE
				category_id IN (1, 4)
				AND department_id IN (1, 3)
		";

		return $sql;
	}

	public function testCategoryDepartmentAgent()
	{
		$sql = "
			SELECT COUNT(*)
			FROM tickets
			WHERE
				category_id IN (1, 4)
				AND department_id IN (1, 3)
				AND tech_id = 5
		";

		return $sql;
	}

	public function testCategoryParticipant()
	{
		$sql = array();

		$sql['join'] = "
			SELECT COUNT(*)
			FROM tickets
			LEFT JOIN ticket_participants ON (ticket_participants.ticket_id = tickets.id)
			WHERE
				tickets.category_id IN (1, 4)
				AND ticket_participants.person_id = 5
		";

		$sql['join_complex_on'] = "
			SELECT COUNT(*)
			FROM tickets
			LEFT JOIN ticket_participants ON (ticket_participants.ticket_id = tickets.id AND ticket_participants.person_id = 5)
			WHERE
				tickets.category_id IN (1, 4)
				AND ticket_participants.person_id IS NOT NULL
		";

		$sql['nested_select'] = "
			SELECT COUNT(*)
			FROM tickets
			WHERE
				category_id IN (1, 4)
				AND id IN (SELECT ticket_id FROM ticket_participants WHERE person_id = 5)
		";

		return $sql;
	}

	public function testSubject()
	{
		$sql = "
			SELECT COUNT(*)
			FROM tickets
			WHERE
				subject LIKE '%golden%'
		";

		return $sql;
	}

	public function testSubjectCategory()
	{
		$sql = "
			SELECT COUNT(*)
			FROM tickets
			WHERE
				category_id IN (1, 4)
				AND subject LIKE '%golden%'
		";

		return $sql;
	}

	public function testSubjectParticipant()
	{
		$sql['join'] = "
			SELECT COUNT(*)
			FROM tickets
			LEFT JOIN ticket_participants ON (ticket_participants.ticket_id = tickets.id)
			WHERE
				subject LIKE '%golden%'
				AND ticket_participants.person_id = 5
		";

		$sql['join_complex_on'] = "
			SELECT COUNT(*)
			FROM tickets
			LEFT JOIN ticket_participants ON (ticket_participants.ticket_id = tickets.id AND ticket_participants.person_id = 5)
			WHERE
				subject LIKE '%golden%'
				AND ticket_participants.person_id IS NOT NULL
		";

		$sql['nested_select'] = "
			SELECT COUNT(*)
			FROM tickets
			WHERE
				subject LIKE '%golden%'
				AND id IN (SELECT ticket_id FROM ticket_participants WHERE person_id = 5)
		";

		return $sql;
	}

	public function testEmail()
	{
		$sql['join'] = "
			SELECT COUNT(*)
			FROM tickets
			LEFT JOIN person_emails ON (person_emails.person_id = tickets.person_id)
			WHERE
				person_emails.email LIKE '%msn%'
		";

		$sql['join_complex_on'] = "
			SELECT COUNT(*)
			FROM tickets
			LEFT JOIN person_emails ON (person_emails.person_id = tickets.person_id AND person_emails.email LIKE '%msn%')
			WHERE person_emails.person_id IS NOT NULL
		";

		$sql['nested_select'] = "
			SELECT COUNT(*)
			FROM tickets
			WHERE
				person_id IN (SELECT person_id FROM person_emails WHERE email LIKE '%msn%')
		";

		return $sql;
	}

	public function testCustomFieldInt()
	{
		/* field_id 1 is ranged from 1-15 */

		$sql = "
			SELECT COUNT(*)
			FROM tickets
			WHERE field_1 = 5
		";
		
		return $sql;
	}

	public function testCustomFieldChoice()
	{
		/* field_id 2 is ranged from 1-50 */
		$sql['join'] = "
			SELECT COUNT(*)
			FROM tickets
			LEFT JOIN ticket_search_fieldassoc ON (ticket_search_fieldassoc.ticket_id = tickets.id AND ticket_search_fieldassoc.field_id = 2)
			WHERE
				ticket_search_fieldassoc.value_int IN (5, 10, 15, 20, 25, 30)
		";

		$sql['join_complex_on'] = "
			SELECT COUNT(*)
			FROM tickets
			LEFT JOIN ticket_search_fieldassoc ON (ticket_search_fieldassoc.ticket_id = tickets.id AND ticket_search_fieldassoc.field_id = 2 AND ticket_search_fieldassoc.value_int IN (5, 10, 15, 20, 25, 30))
			WHERE
				ticket_search_fieldassoc.id IS NOT NULL
		";

		return $sql;
	}

	public function testOpenCustomFieldChoice()
	{
		/* field_id 2 is ranged from 1-50 */
		$sql['join'] = "
			SELECT COUNT(*)
			FROM tickets
			LEFT JOIN ticket_search_fieldassoc ON (ticket_search_fieldassoc.ticket_id = tickets.id AND ticket_search_fieldassoc.field_id = 2)
			WHERE
				tickets.status = 'open'
				AND ticket_search_fieldassoc.value_int IN (5, 10, 15, 20, 25, 30)
		";

		$sql['join_complex_on'] = "
			SELECT COUNT(*)
			FROM tickets
			LEFT JOIN ticket_search_fieldassoc ON (ticket_search_fieldassoc.ticket_id = tickets.id AND ticket_search_fieldassoc.field_id = 2 AND ticket_search_fieldassoc.value_int IN (5, 10, 15, 20, 25, 30))
			WHERE
				tickets.status = 'open'
				AND ticket_search_fieldassoc.id IS NOT NULL
		";

		return $sql;
	}
}