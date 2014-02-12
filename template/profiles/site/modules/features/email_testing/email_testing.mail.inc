<?php
/**
 * @file
 * MailSystemInterface for logging mails to the filesystem.
 */

class WatchdogMailSystem extends DefaultMailSystem {
  /**
   * Save an e-mail message to the watchdog
   *
   * @see http://php.net/manual/en/function.mail.php
   * @see drupal_mail()
   *
   * @param $message
   *   A message array, as described in hook_mail_alter().
   * @return
   *   TRUE if the mail was successfully accepted, otherwise FALSE.
   */
  public function mail(array $message) {
    $template =
      "To: %s\n" .
      "Subject: %s\n" .
      "Body: %s\n";

    watchdog(
        'email_testing',
        sprintf($template, $message['to'], $message['subject'], $message['body']),
        array(),
        WATCHDOG_DEBUG
    );

    return TRUE;
  }
}
