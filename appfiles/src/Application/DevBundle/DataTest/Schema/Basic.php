<?php

namespace Application\DevBundle\DataTest\Schema;

class Basic extends AbstractSchema
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

		/*
		$sql['nested_select'] = "
			SELECT COUNT(*)
			FROM tickets
			WHERE
				category_id IN (1, 4)
				AND id IN (SELECT ticket_id FROM ticket_participants WHERE person_id = 5)
		";
		 */

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

		/*
		$sql['nested_select'] = "
			SELECT COUNT(*)
			FROM tickets
			WHERE
				subject LIKE '%golden%'
				AND id IN (SELECT ticket_id FROM ticket_participants WHERE person_id = 5)
		";
		 */

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

		/*
		$sql['nested_select'] = "
			SELECT COUNT(*)
			FROM tickets
			WHERE
				person_id IN (SELECT person_id FROM person_emails WHERE email LIKE '%msn%')
		";
		 */

		return $sql;
	}

	public function testCustomFieldInt()
	{
		/* field_id 1 is ranged from 1-15 */

		$sql['join'] = "
			SELECT COUNT(*)
			FROM tickets
			LEFT JOIN ticket_field_data ON (ticket_field_data.ticket_id = tickets.id)
			WHERE
				(ticket_field_data.field_id = 1 AND ticket_field_data.value = 5)
		";

		$sql['join_complex_on'] = "
			SELECT COUNT(*)
			FROM tickets
			LEFT JOIN ticket_field_data ON (ticket_field_data.ticket_id = tickets.id AND ticket_field_data.field_id = 1 AND ticket_field_data.value = 5)
			WHERE
				ticket_field_data.id IS NOT NULL
		";

		/* Takes WAYYYY too long
		$sql['nested_select'] = "
			SELECT COUNT(*)
			FROM tickets
			WHERE
				id IN (SELECT ticket_id FROM ticket_field_data WHERE field_id = 1 AND value = 5)
		";
		*/

		return $sql;
	}

	public function testCustomFieldChoice()
	{
		/* field_id 2 is ranged from 1-50 */
		$sql['join'] = "
			SELECT COUNT(*)
			FROM tickets
			LEFT JOIN ticket_field_data ON (ticket_field_data.ticket_id = tickets.id AND ticket_field_data.field_id = 2 AND ticket_field_data.parent_id IS NOT NULL)
			WHERE
				ticket_field_data.value IN (5, 10, 15, 20, 25, 30)
			LIMIT 1000
		";

		$sql['join_complex_on'] = "
			SELECT COUNT(*)
			FROM tickets
			LEFT JOIN ticket_field_data ON (ticket_field_data.ticket_id = tickets.id AND ticket_field_data.field_id = 2 AND ticket_field_data.parent_id IS NOT NULL AND ticket_field_data.value IN (5, 10, 15, 20, 25, 30))
			WHERE
				ticket_field_data.id IS NOT NULL
			LIMIT 1000
		";

		/* Way too long
		$sql['nested_select'] = "
			SELECT COUNT(*)
			FROM tickets
			WHERE
				id IN (SELECT ticket_id FROM ticket_field_data WHERE field_id = 2 AND ticket_field_data.parent_id IS NOT NULL AND value IN (5, 10, 15, 20, 25, 30))
		";
		 */

		$sql['join_like'] = "
			SELECT COUNT(*)
			FROM tickets
			LEFT JOIN ticket_field_data ON (ticket_field_data.ticket_id = tickets.id AND ticket_field_data.field_id = 2 AND ticket_field_data.parent_id IS NULL)
			WHERE
				ticket_field_data.value LIKE '%:5:%'
				OR ticket_field_data.value LIKE '%:10:%'
				OR ticket_field_data.value LIKE '%:15:%'
				OR ticket_field_data.value LIKE '%:20:%'
				OR ticket_field_data.value LIKE '%:25:%'
				OR ticket_field_data.value LIKE '%:30:%'
			LIMIT 1000
		";

		/* Way too long
		$sql['nested_select_like'] = "
			SELECT COUNT(*)
			FROM tickets
			WHERE
				id IN (
					SELECT ticket_id FROM ticket_field_data
					WHERE
						field_id = 2
						AND ticket_field_data.parent_id IS NULL
						AND (
							value LIKE '%:5:%'
							OR value LIKE '%:10:%'
							OR value LIKE '%:15:%'
							OR value LIKE '%:20:%'
							OR value LIKE '%:25:%'
							OR value LIKE '%:30:%'
						)
				)
		";
		 */

		return $sql;
	}

	public function testOpenCustomFieldChoice()
	{
		/* field_id 2 is ranged from 1-50 */
		$sql['join'] = "
			SELECT COUNT(*)
			FROM tickets
			LEFT JOIN ticket_field_data ON (ticket_field_data.ticket_id = tickets.id AND ticket_field_data.field_id = 2 AND ticket_field_data.parent_id IS NOT NULL)
			WHERE
				tickets.status = 'awaiting_agent'
				AND ticket_field_data.value IN (5, 10, 15, 20, 25, 30)
		";

		$sql['join_complex_on'] = "
			SELECT COUNT(*)
			FROM tickets
			LEFT JOIN ticket_field_data ON (ticket_field_data.ticket_id = tickets.id AND ticket_field_data.field_id = 2 AND ticket_field_data.parent_id IS NOT NULL AND ticket_field_data.value IN (5, 10, 15, 20, 25, 30))
			WHERE
				tickets.status = 'awaiting_agent'
				AND ticket_field_data.id IS NOT NULL
		";

		$sql['join_like'] = "
			SELECT COUNT(*)
			FROM tickets
			LEFT JOIN ticket_field_data ON (ticket_field_data.ticket_id = tickets.id AND ticket_field_data.field_id = 2 AND ticket_field_data.parent_id IS NULL)
			WHERE
				tickets.status = 'awaiting_agent'
				AND (
					ticket_field_data.value LIKE '%:5:%'
					OR ticket_field_data.value LIKE '%:10:%'
					OR ticket_field_data.value LIKE '%:15:%'
					OR ticket_field_data.value LIKE '%:20:%'
					OR ticket_field_data.value LIKE '%:25:%'
					OR ticket_field_data.value LIKE '%:30:%'
				)
		";

		return $sql;
	}
}