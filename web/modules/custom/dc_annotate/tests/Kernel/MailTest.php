<?php

/**
 * @file
 * Contains \Drupal\Tests\dc_annotate\Kernel\MailTest.
 */

namespace Drupal\Tests\dc_annotate\Kernel;

use Drupal\comment\Entity\Comment;
use Drupal\comment\Entity\CommentType;
use Drupal\dc\Entity\DCContent;
use Drupal\KernelTests\KernelTestBase;
use Drupal\user\Entity\User;

/**
 * Tests the general mail functionality + token replacement.
 *
 * @group dc_annotate
 */
class MailTest extends KernelTestBase {

  /**
   * {@inheritdoc}
   */
  public static $modules = ['dc_annotate', 'dc', 'user', 'comment', 'system', 'token'];

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();

    $this->installEntitySchema('user');
    $this->installEntitySchema('comment');
    $this->installEntitySchema('dc_content');
    $this->installSchema('system', 'sequences');

    \Drupal::configFactory()->getEditable('system.mail')
      ->set('interface.default', 'test_mail_collector')
      ->save();
  }

  /**
   * @covers \Drupal\dc_annotate\AfarEditRequestMail
   * @covers \Drupal\dc_annotate\AfarEmailSender
   */
  public function testMail() {
    $config = \Drupal::configFactory()->getEditable('dc_annotate.settings');
    $config->set('afar.address', '[current-user:mail]');
    $config->set('afar.from', '[current-user:mail]');
    $config->set('afar.cc', '[current-user:mail]');
    $config->set('afar.bcc', '[current-user:mail]');
    $config->set('afar.subject', '[dc_content:name] [current-user:mail]');
    $config->set('afar.body', '[dc_content:name] [comment:title] [current-user:mail]');
    $config->save();

    $account = User::create([
      'name' => 'test user',
      'mail' => 'test-mail@giraffe.com',
    ]);
    $account->save();
    /** @var \Drupal\Core\Session\AccountSwitcherInterface $account_switcher */
    $account_switcher = \Drupal::service('account_switcher');
    $account_switcher->switchTo($account);

    $dc_content = DCContent::create([
      'name' => 'Test-name',
      'type' => 'destination',
    ]);
    $dc_content->save();

    $comment_type = CommentType::create([
      'id' => 'comment__dc_content',
      'target_entity_type_id' => 'dc_content',
    ]);
    $comment_type->save();

    $comment = Comment::create([
      'entity_id' => ['target_id' => $dc_content->id()],
      'subject' => 'Test-comment-name',
      'comment_type' => 'comment__dc_content',
    ]);
    $comment->save();

    $this->assertMailCount(0);

    /** @var \Drupal\dc_annotate\AfarEmailSender $afar_mail_send */
    $afar_mail_send = \Drupal::service('dc_annotate.afar_sender');
    $afar_mail_send->send($comment);

    $this->assertMailCount(1);
    $recent_mail = $this->getRecentMail();

    $this->assertEquals('test-mail@giraffe.com', $recent_mail['to']);
    $this->assertEquals('test-mail@giraffe.com', $recent_mail['headers']['From']);
    $this->assertEquals('test-mail@giraffe.com', $recent_mail['headers']['Cc']);
    $this->assertEquals('test-mail@giraffe.com', $recent_mail['headers']['Bcc']);
    $this->assertEquals('Test-name test-mail@giraffe.com', $recent_mail['subject']);
    $this->assertEquals("Test-name Test-comment-name test-mail@giraffe.com\n", $recent_mail['body']);
  }

  /**
   * @param int $expected_count
   */
  protected function assertMailCount($expected_count) {
    $mail = \Drupal::state()->get('system.test_mail_collector', []);
    $this->assertCount($expected_count, $mail);
  }

  /**
   * @return array
   */
  protected function getRecentMail() {
    $mail = \Drupal::state()->get('system.test_mail_collector');
    return end($mail);
  }

}
