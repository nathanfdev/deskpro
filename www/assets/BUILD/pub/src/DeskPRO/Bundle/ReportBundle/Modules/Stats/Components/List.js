import React, { PropTypes } from 'react';
import ListItem from './ListItem';

class List extends React.Component {

  static propTypes = {
    customReports:        PropTypes.object.isRequired,
    builtInReports:       PropTypes.object.isRequired,
    customReportsLoaded:  PropTypes.bool.isRequired,
    builtInReportsLoaded: PropTypes.bool.isRequired,
    onReportClick:        PropTypes.func.isRequired
  };


  static showLoading() {
    return (
      <div style={{ textAlign: 'center', padding: '45px' }}>
        <i className="spinner-big-circle" />
      </div>
    );
  }

  showList() {
    return (
      <div className="stat-list-wrapper">
        <ul className="stat-list">
          <li>Custom reports:</li>
          {this.props.customReports.map(
            report =>
              <ListItem key={report.get('id')} onReportClick={this.props.onReportClick} report={report} />
          )}
          <li>Built in reports:</li>
          {this.props.builtInReports.map(
            report =>
              <ListItem key={report.get('id')} onReportClick={this.props.onReportClick} report={report} />
          )}
        </ul>
      </div>
    );
  }

  render() {
    const { builtInReportsLoaded, customReportsLoaded } = this.props;

    return (
      <div className="big-list-of-stats">
        { builtInReportsLoaded && customReportsLoaded ? this.showList() : List.showLoading() }
      </div>
    );
  }
}

export default List;
