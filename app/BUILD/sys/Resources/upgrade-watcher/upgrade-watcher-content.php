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
    <div ng-if="showFinishedNextInfo && initDone && info.status == 'finished' && info.next.date_description" style="padding: 15px; margin-bottom: 5px; background: #eee; border-bottom: 1px solid #ccc;">
        <section>
            <div style="text-align: center">
                The last system update is finished with the details below.<br/>
                The next process is scheduled to begin in {{ info.next.date_description }}
            </div>
        </section>
    </div>
    <article ng-if="initDone && info.status == 'running' || info.status == 'finished'">
        <section class="card-section" ng-repeat="step in info.steps" ng-hide="info.status == 'finished' && step.status == 'waiting'">
            <h3>
                <i ng-show="step.status == 'waiting'" class="far fa-circle icon-incomplete"></i>
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
    <div ng-if="info.summary || info.details" style="padding: 15px; margin-top: 5px; background: #eee; border-top: 1px solid #ccc;">
        <section class="card-section">
            <p ng-if="info.summary"><strong>{{info.summary}}</strong></p>
            <p ng-if="info.details">{{info.details}}</p>
        </section>
    </div>
    <footer ng-show="info.status == 'finished' && info.finishedStatus == 'error'" style="color: #f00c18; padding-top: 13px;">
        The update finished in an ERROR status.
        <br/>
        <a ng-href="{{logUrl}}" class="btn btn-default" target="_blank">View Update Log</a>
    </footer>
    <footer ng-show="info.status == 'finished' && info.finishedStatus == 'warning'" style="color: #f00c18; padding-top: 13px;">
        The update has completed successfully, but we detected a few warnings
        that you should review.
        <br/>
        <a ng-href="{{logUrl}}" class="btn btn-default" target="_blank">View Update Log</a>
    </footer>
    <footer ng-show="info.status == 'finished' && info.finishedStatus == 'success'" style="padding-top: 13px;">
        The update has completed successfully.
    </footer>
</section>
