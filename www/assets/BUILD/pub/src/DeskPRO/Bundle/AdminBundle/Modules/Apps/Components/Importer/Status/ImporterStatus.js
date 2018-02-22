import React, { PropTypes } from 'react';
import classNames from 'classnames';
import ScrollArea from 'react-scrollbar';
import { Checkbox } from 'DeskPRO/Component/Semantic/ReactForm';
import SectionHeader from '../../../../Common/Components/SectionHeader';

const stepTitles = {
  article:          'Articles',
  article_category: 'Article categories',
  news:             'News',
  organization:     'Organizations',
  person:           'People',
  ticket:           'Tickets',
  setting:          'Settings'
};

class ImporterStatus extends React.Component {

  static propTypes = {
    title:          PropTypes.string,
    status:         PropTypes.string,
    log:            PropTypes.string,
    steps:          PropTypes.array,
    importedSteps:  PropTypes.array,
    importedCounts: PropTypes.array,
    appliedSteps:   PropTypes.array,
    appliedCounts:  PropTypes.array
  };

  componentDidMount() {
    this.scrollLogBottom();
  }

  componentDidUpdate() {
    this.scrollLogBottom();
  }

  scrollLogBottom = () => setTimeout(() => {
    if (this.log) {
      this.log.setSizesToState();
      this.log.handleWindowResize();
      this.log.scrollBottom();
    }
  }, 1);

  render() {
    const { title, status, log } = this.props;
    const { steps = [], importedSteps = [], importedCounts = [], appliedSteps = [], appliedCounts = [] } = this.props;

    const wasInit = status !== 'waiting';
    const finished = status === 'complete';

    const totalImportedSteps = steps.length - steps.filter(step => importedSteps.indexOf(step) === -1).length;
    let totalAppliedSteps = steps.length - steps.filter(step => appliedSteps.indexOf(step) === -1).length;
    if (finished) {
      // we can't determine if an apply step has no data or it's not completed yet
      // so mark all apply steps as finished on end process then it means they were empty
      totalAppliedSteps = steps.length;
    }

    const totalProgress = (totalImportedSteps + totalAppliedSteps) / (2 * steps.length);
    const totalPercent = totalProgress * 100;

    return (
      <div className="page admin-importer">
        <SectionHeader
          title={`Data Importer: ${title}`}
          description="An import job is currently running"
          dividing
        />

        <div className="admin-importer-status">
          <h3>{title} Import</h3>

          <div
            className={classNames('ui indicating progress', { active: wasInit, success: finished })}
            data-percent={totalPercent}
          >
            <div className="bar" style={{ transitionDuration: '300ms', width: `${totalPercent}%` }}>
              <div className="progress" />
            </div>
            <div className="label">
              {finished ? 'Import completed!' : `Step ${totalImportedSteps + totalAppliedSteps}/${2 * steps.length}`}
            </div>
          </div>

          <div className="admin-importer-status-container">
            <div className="admin-importer-status-list">
              <ImporterStatusStep title="Initializing" completed={wasInit} />
              <ImporterStatusStepGroup
                title="Downloading data"
                steps={steps}
                completed={importedSteps}
                counts={importedCounts}
              />
              <ImporterStatusStepGroup
                title="Importing into Deskpro"
                steps={steps}
                completed={appliedSteps}
                counts={appliedCounts}
                finished={finished}
              />
              <ImporterStatusStep title="Finishing" completed={finished} />
            </div>

            <div className="admin-importer-status-log">
              <ScrollArea className="admin-importer-status-log-scrollarea" ref={(c) => { this.log = c; }} vertical>
                <div dangerouslySetInnerHTML={{ __html: (log || '').replace(/\n/g, '<br />') }} />
              </ScrollArea>
            </div>
          </div>
        </div>
      </div>
    );
  }
}

class ImporterStatusStep extends React.Component {

  static propTypes = {
    title:     PropTypes.string,
    completed: PropTypes.bool
  };

  render() {
    const { title, completed } = this.props;

    return (
      <div className="admin-importer-status-list-group">
        <div className="admin-importer-status-list-group-title">
          <Checkbox label={title} value={completed} />
        </div>
      </div>
    );
  }
}

class ImporterStatusStepGroup extends React.Component {

  static propTypes = {
    title:     PropTypes.string,
    steps:     PropTypes.array,
    completed: PropTypes.array,
    counts:    PropTypes.object,
    finished:  PropTypes.bool
  };

  render() {
    const { title, steps = [], completed = [], counts = [], finished } = this.props;

    return (
      <div className="admin-importer-status-list-group">
        <div className="admin-importer-status-list-group-title">
          <Checkbox
            label={title}
            value={steps.filter(step => completed.indexOf(step) === -1).length === 0 || finished}
          />
        </div>
        {steps.map((step, key) =>
          <div key={key} className="admin-importer-status-list-group-step">
            <Checkbox label={stepTitles[step]} value={completed.indexOf(step) !== -1 || finished} />
            <span className="admin-importer-status-list-group-step-count">
              (imported {counts[step] || 0})
            </span>
          </div>
        )}
      </div>
    );
  }
}

export default ImporterStatus;
