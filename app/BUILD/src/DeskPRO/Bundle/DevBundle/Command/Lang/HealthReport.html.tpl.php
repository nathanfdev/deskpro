<!DOCTYPE html>
<html>
<head>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bulma@0.8.2/css/bulma.min.css">
    <script
        src="https://code.jquery.com/jquery-3.5.0.slim.min.js"
        integrity="sha256-MlusDLJIP1GRgLrOflUQtshyP0TwT/RHXsI1wWGnQhs="
        crossorigin="anonymous"></script>
    <style>
        .tab-body-section { display: none; }
        .tab-body-section.is-active { display: block; }
    </style>
</head>
<body>

<section class="section">
    <div class="container">

        <div class="tabs" id="page_tabs">
            <ul>
                <?php if (!empty($badKeys)): ?><li data-body-id="bad_phrase_ids"><a>Bad Phrase IDs</a></li><?php endif ?>
                <?php if (!empty($badStrings)): ?><li data-body-id="bad_strings"><a>Bad Strings</a></li><?php endif ?>
                <?php if (!empty($similarPhrases)): ?><li data-body-id="similar_strings"><a>Similar Strings</a></li><?php endif ?>
                <?php if (!empty($usages)): ?><li data-body-id="usages"><a>Usage Links</a></li><?php endif ?>
                <?php if (!empty($usagesNotFound)): ?><li data-body-id="unknown_usages"><a>Unknown Usage</a></li><?php endif ?>
                <li data-body-id="help"><a>Help</a></li>
            </ul>
        </div>

        <?php if (!empty($badKeys)): ?>
            <section class="tab-body-section" id="bad_phrase_ids">
                <table class="table">
                    <thead>
                        <tr>
                            <th>PhraseID</th>
                            <th>Problems with the ID</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($badKeys as $keyId => $probs): ?>
                            <tr>
                                <td><code><?php echo $keyId ?></code></td>
                                <td><?php echo implode(', ', $probs) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </section>
        <?php endif ?>

        <?php if (!empty($badStrings)): ?>
            <section class="tab-body-section" id="bad_strings">
                <table class="table">
                    <thead>
                    <tr>
                        <th>PhraseID</th>
                        <th>Problems with the string</th>
                        <th>String</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($badStrings as $keyId => $desc): ?>
                        <tr>
                            <td><code><?php echo $keyId ?></code></td>
                            <td><?php echo implode(', ', $desc['problems']) ?></td>
                            <td><?php echo htmlspecialchars($desc['text']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </section>
        <?php endif ?>

        <?php if (!empty($similarPhrases)): ?>
            <section class="tab-body-section" id="similar_strings">
                <?php foreach ($similarPhrases as $group): ?>
                    <table class="table">
                        <?php foreach ($group as $keyId => $text): ?>
                            <tr>
                                <td width="350"><code><?php echo $keyId ?></code></td>
                                <td><?php echo htmlspecialchars($text) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </table>
                    <hr />
                <?php endforeach; ?>
            </section>
        <?php endif ?>

        <?php if (!empty($baseUrl) && !empty($usages)): ?>
            <section class="tab-body-section" id="usages">
                <table class="table">
                    <thead>
                    <tr>
                        <th>PhraseID</th>
                        <th>String &amp; Uses</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($usages as $keyId => $tpls): ?>
                        <tr>
                            <td><code><?php echo $keyId ?></code></td>
                            <td>
                                <article class="message is-marginless">
                                    <div class="message-body is-size-7">
                                        <?php echo $phrases[$keyId] ?? '' ?>
                                    </div>
                                </article>
                                <?php foreach ($tpls as $t): ?>
                                    <a href="<?php echo $baseUrl ?><?php echo $t ?>" target="_blank"><?php echo $t ?></a>
                                <?php endforeach; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </section>
        <?php endif ?>

        <?php if (!empty($usagesNotFound)): ?>
            <section class="tab-body-section" id="unknown_usages">
                <table class="table">
                    <thead>
                    <tr>
                        <th>PhraseID</th>
                        <th>String</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($usagesNotFound as $keyId => $string): ?>
                        <tr>
                            <td><code><?php echo $keyId ?></code></td>
                            <td><?php echo htmlspecialchars($string) ?></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </section>
        <?php endif ?>

        <section class="tab-body-section" id="help">
            <h3>Problem Codes</h3>
            <table class="table">
                <thead>
                    <tr>
                        <th width="200">Code</th>
                        <th>Explanation</th>
                    </tr>
                </thead>
                <tbody>
                <tr>
                    <td>uppercase-char</td>
                    <td>
                        Phrase IDs should be all lowercase. Uppercase, such as with a camelCase identifier, is now allowed.
                        E.g. "my.superCoolExample" should be "my.super_cool_example".
                    </td>
                </tr>
                <tr>
                    <td>dash</td>
                    <td>
                        Phrase IDs should separate words with underscores, not dashes.
                        E.g. "my.super-cool-example" should be "my.super_cool_example".
                    </td>
                </tr>
                <tr>
                    <td>invalid-ns</td>
                    <td>
                        Phrase IDs should have exactly two dots in the name: "a.b.c".
                        "a" should always be the same in the same file (e.g. helpcenter), "b" is a sub-namespace, and "c"
                        is the qualifying name for the phrase.
                    </td>
                </tr>
                <tr>
                    <td>complex-html</td>
                    <td>
                        Phrases should not include complex HTML such as class names, ids, etc.
                        These should be moved into the calling template or CSS.
                    </td>
                </tr>
                <tr>
                    <td>legacy-html</td>
                    <td>
                        When using HTML, avoid using b/i, use strong/em instead.
                    </td>
                </tr>
                <tr>
                    <td>pre-rendered-datetime</td>
                    <td>
                        This rule matches if a variable {date} or {time} is detected; it's a guess that it's a pre-rendered variable
                        that is being inserted into a phrase as a string instead instead of using the the ICU formatted value (<a target="_blank" href="https://formatjs.io/guides/message-syntax/">see</a>).
                        Example:
<pre>// Example phrase
some_example: 'Article was created on {someDate, date, medium} at {someDate, time, short}.'

// Example usage
{{ phrase('some_example', { someDate: article.date_created }) }}</pre>
                        The fix is to modify the calling code to pass a Date/DateTime object directly to the phrase renderer. i.e. do NOT pre-format the date.
                    </td>
                </tr>
                <tr>
                    <td>unqualified-variable</td>
                    <td>
                        This rule matches if a variable that represents an object is used (e.g. "person"). Legacy phrases would work
                        because __toString would be called (magic). But this isn't portable, and should be fixed.
<pre>// Example phrase
some_example: 'Welcome back, {person_name}'

// Example usage
{{ phrase('welcome_back', { person_name: person.name })</pre>
                    </td>
                </tr>
                <tr>
                    <td>object-variable</td>
                    <td>
                        This rule matches when an object is being used as a variable. Old phrases would work
                        because the value would be evaluated (magic). This is not portable. Variables should be
                        passed to phrases explicitly. E.g. do not use a variable "{ticket.subject}"
                        inside a string; instead, pass a "ticket_subject" variable specifically.

<pre>// Example phrase
some_example: 'RE: {ticket_subject}'

// Example usage
{{ phrase('some_example', { ticket_subject: ticket.name })</pre>
                    </td>
                </tr>
                <tr>
                    <td>uppercase-only</td>
                    <td>
                        Uppercase-only words shouldn't be used. If a design calls for uppercase text,
                        use CSS with the text-transform property.
                    </td>
                </tr>
                </tbody>
            </table>
        </section>
    </div>
</section>

<script>
$(document).ready(function () {
    var $tabs = $('#page_tabs').find('li');
    var $tabBodies = $('.tab-body-section');

    $tabs.on('click', function(e) {
        e.preventDefault();
        $tabBodies.removeClass('is-active');
        $tabs.removeClass('is-active');
        $('#'+$(this).addClass('is-active').data('body-id')).addClass('is-active');
    });

    $tabs.first().click();
});
</script>

</body>
</html>
