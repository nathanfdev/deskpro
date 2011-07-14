<?php

namespace Application\DevBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use Application\DeskPRO\App;
use Application\DeskPRO\Entity\ClientMessage;

use Orb\Util\Strings;

class TestController extends Controller
{
    public function indexAction()
    {
		$db = App::getDb();
		$em = App::getOrm();

		$db->exec("TRUNCATE TABLE news");
		$db->exec("TRUNCATE TABLE news_categories");
		$db->exec("TRUNCATE TABLE news_comments");

		$titles = array(
			'Feedback ("Ideas") Plugin',
			'DeskPRO v3.4 is released',
			'Some 2009 Updates',
			'What\'s new in DeskPRO 3.3',
			'Chat, Notifier, v3.2 - What Next?',
			'New Server Requirements',
			'DeskPRO 3.2.0 & Friends',
			'3.1 "Gold"',
			'Feature Requests: You\'ve Got\'em, We Want\'em',
			'New Admin/Tech Look',
			'Multiple User Sources',
			'Work on the Chat Plugin Recommences',
			'New DeskPRO Blog',
		);

		$lorem1 = "Lorem ipsum dolor sit amet, consectetur adipiscing elit. Sed ornare molestie venenatis. Morbi vel diam leo. Aliquam ullamcorper accumsan enim, nec sagittis turpis tincidunt non. Etiam sagittis erat vel nunc scelerisque ultrices. Vivamus vulputate felis vitae arcu ultrices eu feugiat erat pretium. Phasellus velit nunc, convallis molestie suscipit sed, malesuada in eros. Nulla egestas, ligula at fermentum tincidunt, elit augue tincidunt lorem, eu ultricies tortor ipsum vel tortor. Praesent ac lectus nec nisl semper eleifend vel elementum elit. Praesent et enim quis augue bibendum fermentum vel adipiscing ipsum. Maecenas et enim venenatis nunc molestie sagittis. Nullam interdum mattis metus nec congue. Praesent vehicula ultricies est, at molestie enim accumsan eget. Donec pulvinar pulvinar lorem. Mauris justo urna, suscipit nec adipiscing venenatis, faucibus quis risus. Vivamus vitae ante nunc.";
		$lorem2 = "Lorem ipsum dolor sit amet, consectetur adipiscing elit. Curabitur aliquam suscipit adipiscing. Nunc tellus velit, fringilla nec suscipit lacinia, mattis ut lectus. Etiam vitae tellus id ligula venenatis elementum. Nam ornare tristique pretium. Maecenas viverra pharetra sem id hendrerit. Sed dolor nisi, euismod quis condimentum eu, pellentesque vitae lorem. In hac habitasse platea dictumst. Quisque felis lectus, dignissim eget imperdiet vulputate, scelerisque ac massa. Praesent a dui leo, hendrerit semper libero. Vestibulum non massa at libero vulputate bibendum a ut est.

In sed velit at sapien suscipit aliquam dignissim vitae ante. Maecenas at tellus et purus dignissim posuere vel eget justo. Aliquam sed risus at felis malesuada condimentum vehicula at urna. Etiam pretium porttitor enim. Vivamus et leo non ligula pulvinar suscipit vel eget libero. Mauris massa lacus, porttitor quis sodales non, lobortis sed risus. Lorem ipsum dolor sit amet, consectetur adipiscing elit. Nulla luctus hendrerit dui, eget egestas metus egestas quis. Vestibulum commodo velit at arcu ullamcorper suscipit. Vestibulum vel diam nec neque varius faucibus";
		$lorem3 = "Lorem ipsum dolor sit amet, consectetur adipiscing elit. Aenean condimentum erat sit amet justo dictum consectetur. Morbi tristique dui ut quam viverra eget ornare mauris iaculis. Aliquam ultrices velit vitae felis venenatis ac gravida mi commodo. Aliquam arcu erat, accumsan sed aliquam vitae, posuere nec lectus. Suspendisse metus nulla, scelerisque vitae viverra in, vehicula sit amet ligula. Sed et dolor sapien. Cras semper ligula pretium ante placerat adipiscing. Maecenas ac dictum ipsum. Sed elementum nibh eu purus luctus vitae varius mi scelerisque. Curabitur bibendum tempus nibh, a faucibus urna semper nec. Aenean mollis dignissim augue, a volutpat lectus egestas quis. Etiam id semper neque. Mauris commodo nulla id mi eleifend vitae tempor enim imperdiet. Curabitur arcu ipsum, iaculis sed hendrerit et, congue sit amet sapien. Phasellus nec sapien est. Fusce ullamcorper volutpat faucibus. Nunc vitae tellus eros. Proin nec orci est. Integer a diam elementum felis varius ornare. Proin sit amet odio lacus, et suscipit purus.

Quisque ac purus vel augue hendrerit rhoncus et eget erat. Sed ullamcorper convallis urna, non rutrum velit lacinia et. Maecenas sed lorem at justo tincidunt sodales et a justo. Praesent ornare egestas viverra. Mauris et malesuada urna. Sed interdum fermentum ipsum nec mollis. In hac habitasse platea dictumst. Suspendisse rhoncus cursus euismod. Integer consequat nisl id nisl mollis laoreet et et massa. Aliquam purus orci, cursus at rhoncus ac, varius ut erat. Donec ac mauris nibh. Proin cursus tempus lorem, a ultricies nunc consectetur id. Etiam facilisis molestie pretium.

Ut faucibus posuere scelerisque. Fusce porttitor auctor velit imperdiet ornare. Morbi diam est, suscipit ut suscipit et, sodales at est. Suspendisse potenti. Quisque nec lorem sapien, sit amet imperdiet massa. Fusce enim dui, interdum quis congue et, ultrices vel velit. Fusce vel egestas tellus. Cras mattis, nibh tincidunt adipiscing ultrices, diam leo condimentum turpis, ac porttitor sapien tellus quis augue. Nullam leo nulla, tincidunt vitae dictum sit amet, hendrerit at nisl. In gravida vehicula turpis vitae lacinia. Mauris velit orci, accumsan ac ullamcorper id, suscipit nec libero. Vestibulum ante ipsum primis in faucibus orci luctus et ultrices posuere cubilia Curae; Integer rutrum, tellus eu semper malesuada, nunc orci placerat erat, in suscipit nunc metus nec nibh. Curabitur sed ultrices leo. Praesent sit amet elementum massa. Nullam auctor pellentesque eros, id luctus mi elementum quis";
		$lorem4 = "Lorem ipsum dolor sit amet, consectetur adipiscing elit. Suspendisse et est sit amet mauris luctus congue. Etiam ullamcorper justo in orci mollis feugiat. Morbi a mi nisi. Aliquam elit nunc, imperdiet vel congue vel, dapibus quis nisl. Nullam lorem ipsum, bibendum eu pellentesque vehicula, aliquet et sapien. Phasellus iaculis fermentum leo a scelerisque. Duis viverra, dui at ultricies volutpat, arcu felis vulputate metus, sit amet euismod eros purus eget purus. Vivamus eu orci leo, rutrum laoreet sapien. Fusce in justo eget justo volutpat porttitor. Morbi consequat, nibh eu iaculis tristique, libero nibh venenatis nunc, a aliquet augue libero sed elit. Quisque blandit aliquet velit at lacinia. Nam vitae eros quis tellus lacinia rhoncus.

Suspendisse condimentum nibh ac nibh consectetur et facilisis odio fringilla. Aliquam erat volutpat. Praesent interdum libero accumsan eros dignissim nec tincidunt dolor laoreet. Donec mattis, enim faucibus fermentum tristique, augue nisl euismod leo, et accumsan purus diam quis lectus. Cras placerat, urna non interdum sodales, leo turpis pulvinar lorem, vitae tempus ligula sapien vel leo. Proin tempus nunc a dui venenatis at sollicitudin eros feugiat. Curabitur a elit mi, vel scelerisque nisl. Donec quis justo ut nulla laoreet sollicitudin. Nunc in molestie nisi. Etiam luctus fermentum felis, ac convallis lectus scelerisque at. Vivamus erat mauris, facilisis sed fringilla in, bibendum nec velit. Maecenas accumsan, erat eget cursus varius, nisi justo venenatis quam, quis pellentesque nisi purus vel arcu. Proin metus mauris, auctor dictum fermentum a, tempor id est. Phasellus tincidunt, nibh eget gravida hendrerit, enim quam vestibulum odio, vitae scelerisque dolor orci eget magna.

Curabitur sit amet libero vel metus elementum tempor a eu augue. Fusce ut tortor in felis fermentum iaculis suscipit non justo. Nullam vulputate blandit iaculis. Mauris facilisis porttitor odio nec accumsan. Vestibulum facilisis ipsum at nisl semper elementum. Nunc posuere fermentum mi, a ullamcorper libero tempor eu. Vivamus volutpat velit quis justo laoreet at feugiat odio pharetra. Vestibulum eu augue quis tortor auctor consectetur. Maecenas hendrerit feugiat bibendum. Donec lectus libero, auctor sed volutpat nec, ultrices a nunc. Suspendisse potenti. Ut tempus neque a sem eleifend venenatis.

Ut tincidunt nunc ut dolor egestas a porta tortor porttitor. Maecenas adipiscing imperdiet consequat. Phasellus blandit turpis vitae nunc ornare sit amet fermentum turpis vulputate. Nulla aliquam scelerisque ligula, non iaculis metus posuere eget. Sed elementum euismod tellus, vel imperdiet est egestas in. Ut magna nisl, congue vitae fringilla ac, convallis sit amet magna. Cras erat enim, imperdiet ac interdum facilisis, bibendum ac magna. Quisque mollis, dolor semper tincidunt rhoncus, dolor libero congue leo, id aliquam eros massa ut ante. Integer vitae lacinia libero. Praesent eget nibh tellus. Mauris imperdiet egestas rutrum. Suspendisse augue elit, egestas at feugiat id, sodales eu velit.";

		$em->beginTransaction();

		$cat1 = new \Application\DeskPRO\Entity\NewsCategory();
		$cat1->fromArray(array('title' => 'DeskPRO'));
		$em->persist($cat1);

		$cat2 = new \Application\DeskPRO\Entity\NewsCategory();
		$cat2->fromArray(array('title' => 'DeskPRO Live'));
		$em->persist($cat2);

		$agent = $em->find('DeskPRO:Person', 20001);

		for ($i = 0; $i < 20; $i++) {
			$catN = ${'cat' . mt_rand(1,2)};
			$loremN = ${'lorem' . mt_rand(1,4)};

			if (isset($titles[$i])) {
				$titleN = $titles[$i];
			} else {
				$titleN = $titles[mt_rand(0,12)];
			}


			$news = new \Application\DeskPRO\Entity\News();
			$news->fromArray(array(
				'title' => $titleN,
				'category' => $catN,
				'person' => $agent,
				'content' => $loremN,
				'is_published' => true,
			));

			$em->persist($news);
		}

		$em->flush();
		$em->commit();

		exit;
		return $this->render('DevBundle:Test:test.html.twig');
    }
}
