<section class="ng-hide" ng-show="true">
    <header>
        <div style="float:right">
            <a ng-href="{{logUrl}}" target="_blank" style="color: #fff; margin-top: 4px;">View Update Log</a>
        </div>
        <h1>DeskPRO Updater</h1>
    </header>
    <article ng-if="!initDone">
        <section class="card-section">
            <div style="text-align: center"><i class="spinner-editor"></i></div>
        </section>
    </article>
    <article ng-if="initDone && info.status == 'none'">
        <section class="card-section">
            <div style="text-align: center">
                No system update is scheduled. There is nothing to see here.
            </div>
        </section>
    </article>
    <article ng-if="initDone && info.status == 'waiting'">
        <section class="card-section">
            <div style="text-align: center">
                The next system update is scheduled to start in: {{ info.date_description }}.
                <br/>
                <br/>
                <br/>
                <i class="spinner-editor"></i>
            </div>
        </section>
    </article>
    <article ng-if="initDone && info.status == 'running' || info.status == 'finished'">
        <section class="card-section" ng-repeat="step in info.steps" ng-hide="info.status == 'finished' && step.status == 'waiting'">
            <h3>
                <i ng-show="step.status == 'waiting'" class="fa fa-circle-o icon-incomplete"></i>
                <i ng-show="step.status == 'running'" class="fa fa-circle icon-complete"></i>
                <i ng-show="step.status == 'finished'" class="fa fa-arrow-circle-right icon-on"></i>
                {{step.title}}
            </h3>
            <p ng-show="step.status == 'finished' && step.summary">
                {{step.summary}}
            </p>
            <p ng-show="step.status == 'finished' && step.detail">
                {{step.detail}}
            </p>
        </section>
    </article>
    <footer ng-show="info.status == 'finished' && info.finishedStatus == 'error'" style="color: #f00c18; padding-top: 13px;">
        The update finished in an ERROR status. Please review the debug messages above.
    </footer>
    <footer ng-show="info.status == 'finished' && info.finishedStatus == 'warning'" style="color: #f00c18; padding-top: 13px;">
        The update has completed successfully, but we detected a few warnings
        that you should review above.
    </footer>
    <footer ng-show="info.status == 'finished' && info.finishedStatus == 'success'" style="padding-top: 13px;">
        The update has completed successfully.
    </footer>
</section>
