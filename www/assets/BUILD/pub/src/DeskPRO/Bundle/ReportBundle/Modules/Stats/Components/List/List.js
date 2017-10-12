import React, { PropTypes } from 'react';
import ListItem from './ListItem';

class List extends React.Component {

  static propTypes = {
    customReports:  PropTypes.object.isRequired,
    builtInReports: PropTypes.object.isRequired,
    reportsLoaded:  PropTypes.bool.isRequired,
    onReportClick:  PropTypes.func.isRequired,
    onLabelClick:   PropTypes.func.isRequired,
    labels:         PropTypes.object.isRequired
  };


  static showLoading() {
    return (
      <div style={{ textAlign: 'center', padding: '45px' }}>
        <i className="spinner-big-circle" />
      </div>
    );
  }

  showList() {
    const { onLabelClick, onReportClick, labels } = this.props;

    return (
      <div className="stat-list-wrapper">
        <ul className="stat-list">
          {this.props.customReports.map(
            report =>
              <ListItem
                key={report.get('id')}
                onLabelClick={onLabelClick}
                onReportClick={onReportClick}
                report={report}
                labels={labels}
              />
          )}
          {this.props.builtInReports.map(
            report =>
              <ListItem
                key={report.get('id')}
                onLabelClick={onLabelClick}
                onReportClick={onReportClick}
                report={report}
                labels={labels}
              />
          )}
        </ul>
      </div>
    );
  }

  render() {
    return (
      <div className="big-list-of-stats">
        { this.props.reportsLoaded ? this.showList() : List.showLoading() }
      </div>
    );
  }
}

export default List;
