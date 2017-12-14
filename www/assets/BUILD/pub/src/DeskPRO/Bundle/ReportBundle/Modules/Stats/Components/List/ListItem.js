import PropTypes from 'prop-types';
import React from 'react';
import classNames from 'classnames';
import TitleWithVars from '../TitleWithVars';

class ListItem extends React.Component {

  static propTypes = {
    report:            PropTypes.object.isRequired,
    onEditReportClick: PropTypes.func.isRequired,
    onRunReportClick:  PropTypes.func.isRequired,
    onLabelClick:      PropTypes.func.isRequired,
    labels:            PropTypes.object.isRequired,
    isActive:          PropTypes.bool.isRequired,
    groupParams:       PropTypes.object.isRequired,
    onChangeReportVar: PropTypes.func.isRequired,
  };

  constructor(props) {
    super(props);
    this.onEditClick       = this.onEditClick.bind(this);
    this.onRunClick        = this.onRunClick.bind(this);
  }

  onRunClick(event) {
    if (event) {
      event.preventDefault();
      event.stopPropagation();
    }
    const { onRunReportClick, report } = this.props;
    onRunReportClick(report);
  }

  onEditClick(event) {
    event.preventDefault();
    event.stopPropagation();
    this.props.onEditReportClick(this.props.report);
  }

  isLabelActive(label) {
    const activeLabels = this.props.labels.filter(value => value.get('active')).map(value => value.get('label')).toJS();
    return activeLabels.indexOf(label) >= 0;
  }

  render() {
    const { isActive, groupParams, onChangeReportVar, report } = this.props;

    return (
      <li className={classNames({ active: isActive })} onClick={this.onRunClick}>
        <h1>
          <TitleWithVars
            onRunClick={this.onRunClick}
            onChangeReportVar={onChangeReportVar}
            groupParams={groupParams}
            report={report}
          />
          <span onClick={this.onEditClick} className="controls"><i className="fa fa-pencil" /></span>
        </h1>
        { report.get('labels').size > 0 || !report.get('is_custom') ?
          <p>
            {report.get('labels').map(
              label =>
                <span
                  onClick={(event) => {
                    event.preventDefault();
                    event.stopPropagation();
                    this.props.onLabelClick(label);
                  }}
                  key={label}
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
