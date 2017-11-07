import React, { PropTypes } from 'react';
import { Loader } from '@deskpro/react-components';
import ListItem from './ListItem';

class List extends React.Component {

  static propTypes = {
    customReports:     PropTypes.object.isRequired,
    builtInReports:    PropTypes.object.isRequired,
    currentReport:     PropTypes.object,
    reportsLoaded:     PropTypes.bool.isRequired,
    onEditReportClick: PropTypes.func.isRequired,
    onRunReportClick:  PropTypes.func.isRequired,
    onLabelClick:      PropTypes.func.isRequired,
    onChangeReportVar: PropTypes.func.isRequired,
    labels:            PropTypes.object.isRequired,
    groupParams:       PropTypes.object.isRequired,
  };


  static showLoading() {
    return (
      <div style={{ textAlign: 'center', padding: '45px' }}>
        <Loader size="xlarge" />
      </div>
    );
  }

  showList() {
    const { labels, currentReport, groupParams, customReports, builtInReports } = this.props;
    const { onLabelClick, onEditReportClick, onRunReportClick, onChangeReportVar } = this.props;
    return (
      <div className="stat-list-wrapper">
        <ul className="stat-list">
          {customReports.map(
            report =>
              <ListItem
                key={report.get('id')}
                onLabelClick={onLabelClick}
                onEditReportClick={onEditReportClick}
                onRunReportClick={onRunReportClick}
                onChangeReportVar={onChangeReportVar}
                isActive={currentReport && currentReport.get('id') === report.get('id')}
                report={report}
                labels={labels}
                groupParams={groupParams}
              />
          )}
          {builtInReports.map(
            report =>
              <ListItem
                key={report.get('id')}
                onLabelClick={onLabelClick}
                onEditReportClick={onEditReportClick}
                onRunReportClick={onRunReportClick}
                onChangeReportVar={onChangeReportVar}
                isActive={currentReport && currentReport.get('id') === report.get('id')}
                report={report}
                labels={labels}
                groupParams={groupParams}
              />
          )}
        </ul>
      </div>
    );
  }

  render() {
    return (
      <div className="big-list-of-stats">
        { this.props.reportsLoaded && this.props.groupParams.has('dates') ? this.showList() : List.showLoading() }
      </div>
    );
  }
}

export default List;
