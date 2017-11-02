import React, { PropTypes } from 'react';
import classNames from 'classnames';
import { transformReportData } from '../helper';
import ListItemTitle from './ListItemTitle';

class ListItem extends React.Component {

  static propTypes = {
    report:            PropTypes.object.isRequired,
    onEditReportClick: PropTypes.func.isRequired,
    onRunReportClick:  PropTypes.func.isRequired,
    onLabelClick:      PropTypes.func.isRequired,
    labels:            PropTypes.object.isRequired,
    isActive:          PropTypes.bool.isRequired,
    groupParams:       PropTypes.object.isRequired,
  };

  constructor(props) {
    super(props);
    this.onEditClick       = this.onEditClick.bind(this);
    this.onRunClick        = this.onRunClick.bind(this);
    this.onChangeReportVar = this.onChangeReportVar.bind(this);
    this.state = {
      report: this.props.report
    };
  }

  onRunClick(event) {
    if (event) {
      event.preventDefault();
      event.stopPropagation();
    }
    const { onRunReportClick } = this.props;
    const { report } = this.state;
    const data = transformReportData(report);
    onRunReportClick(report, data);
  }

  onChangeReportVar(report) {
    this.setState({ report }, this.onRunClick);
  }

  onEditClick(event) {
    event.preventDefault();
    event.stopPropagation();
    this.props.onEditReportClick(this.state.report);
  }

  isLabelActive(label) {
    const activeLabels = this.props.labels.filter(value => value.get('active')).map(value => value.get('label')).toJS();
    return activeLabels.indexOf(label) >= 0;
  }

  render() {
    const { isActive, groupParams } = this.props;
    const { report } = this.state;

    return (
      <li className={classNames({ active: isActive })}>
        <h1>
          <ListItemTitle onRunClick={this.onRunClick} onChangeReportVar={this.onChangeReportVar} groupParams={groupParams} report={report} />
          <span onClick={this.onEditClick} className="controls"><i className="pencil icon" /></span>
        </h1>
        { report.get('labels').size > 0 || !report.get('is_custom') ?
          <p>
            {report.get('labels').map(
              (label, index) =>
                <span
                  onClick={(event) => {
                    event.preventDefault();
                    event.stopPropagation();
                    this.props.onLabelClick(label);
                  }}
                  key={index}
                  className={classNames('stat-label', { active: this.isLabelActive(label) })}
                >
                  <i className="fa fa-tag" /> { label }
                </span>
            )}
            { !report.get('is_custom')
              ? <span className="stat-label active"><i className="fa fa-tag" /> Built-in report</span>
              : null
            }
          </p>
          : null
        }
      </li>
    );
  }
}

export default ListItem;
