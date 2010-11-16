<?php

namespace Application\DevBundle\DataTest\Schema;

class Book extends AbstractSchema
{
	public function testBookCustomInt()
	{
		$sql = "
			SELECT COUNT(*)
			FROM books
			LEFT JOIN book_info ON (book_info.book_id = books.id AND book_info.info_id = 1)
			WHERE
				book_info.value = " . mt_rand(1,15) . "
		";

		return $sql;
	}

	public function testBookOrgCustomInt()
	{
		$sql = "
			SELECT COUNT(*)
			FROM books
			LEFT JOIN book_info ON (book_info.book_id = books.id AND book_info.info_id = 1)
			WHERE
				books.organization_id IN (1, " . mt_rand(2,4) . ")
				AND book_info.value = " . mt_rand(1,15) . "
		";

		return $sql;
	}

	public function testBookCustomInt2()
	{
		$sql = "
			SELECT COUNT(*)
			FROM books
			LEFT JOIN book_info ON (book_info.book_id = books.id AND book_info.info_id = 2)
			WHERE
				book_info.value = " . mt_rand(1,5) . "
		";

		return $sql;
	}

	public function testBookOrgCustomInt2()
	{
		$sql = "
			SELECT COUNT(*)
			FROM books
			LEFT JOIN book_info ON (book_info.book_id = books.id AND book_info.info_id = 2)
			WHERE
				books.organization_id IN (1, " . mt_rand(2,4) . ")
				AND book_info.value = " . mt_rand(1,5) . "
		";

		return $sql;
	}

	public function testBookOrgCustomSelect()
	{
		$sql = "
			SELECT COUNT(*)
			FROM books
			LEFT JOIN book_info ON (book_info.book_id = books.id AND book_info.info_id = " . mt_rand(1001, 1009) . ")
			WHERE
				books.organization_id IN (1, " . mt_rand(2,4) . ")
				AND book_info.info_id IS NOT NULL
		";

		return $sql;
	}
}