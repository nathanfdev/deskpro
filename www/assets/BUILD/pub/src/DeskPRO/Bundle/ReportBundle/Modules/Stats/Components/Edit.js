import PropTypes from 'prop-types';
import React from 'react';
import { reduxForm, submit } from 'redux-form';
import { Button, Loader } from '@deskpro/react-components';
import { api } from 'DeskPRO/Bundle/AppBundle/DAL';
import Immutable from 'immutable';
import classNames from 'classnames';
import { connect } from 'react-redux';
import { EditForm } from './EditForm';
import { saveReport } from '../../Application/Actions/reportActions';

class EditContainer extends React.Component {

  static propTypes = {
    report:       PropTypes.object.isRequired,
    groupParams:  PropTypes.object.isRequired,
    onCloneClick: PropTypes.func.isRequired,
    onRunClick:   PropTypes.func.isRequired,
    dispatch:     PropTypes.func.isRequired,
  };

  static dpqlParser(query) {
    const config = {
      headers: {
        'X-DeskPRO-API-Token':     window.DP_API_TOKEN,
        'X-DeskPRO-Session-ID':    window.DP_SESSION_ID,
        'X-DeskPRO-Request-Token': window.DP_REQUEST_TOKEN,
      }
    };

    return api.sendPost('DP_API/report_widgets/parse', {
      query,
      current_type: 'query',
      new_type:     'builder'
    }, config).then(res => res.data.parts);
  }

  static getStateFromReport(report) {
    const queryParts = report.has('query_parts') ? report.get('query_parts') : Immutable.fromJS({});
    const initialFormValue = {
      title:  report.get('title'),
      labels: report.get('labels', Immutable.List()).toArray().join(', '),
      query:  {
        select:  queryParts.get('select', ''),
        from:    queryParts.get('from', ''),
        where:   queryParts.get('where', ''),
        splitBy: queryParts.get('splitBy', ''),
        groupBy: queryParts.get('groupBy', ''),
        orderBy: queryParts.get('orderBy', ''),
        offset:  queryParts.get('offset', ''),
        limit:   queryParts.get('limit', ''),
      },
      vars: report.get('variables', Immutable.Map()).toJS(),
    };

    return { initialFormValue };
  }

  constructor(props) {
    super(props);
    this.state = {
      saving: false,
      ...EditContainer.getStateFromReport(props.report)
    };
  }

  componentWillReceiveProps(props) {
    if (props.report !== this.props.report) {
      this.setState({
        ...EditContainer.getStateFromReport(props.report)
      });
    }
  }

  onCloneClick = (event) => {
    event.preventDefault();
    event.stopPropagation();
    this.props.onCloneClick(this.props.report);
  };

  onRunClick = (event) => {
    event.preventDefault();
    event.stopPropagation();
    this.props.onRunClick(this.props.report);
  };

  onSubmit = (formData) => {
    const { dispatch, report } = this.props;

    const labels        = formData.labels.length ? formData.labels.split(',') : null;
    const displayTypes  = report.get('display_types', Immutable.fromJS([])).toArray().map(t => t.toLowerCase());

    const reportData = {
      id:            this.props.report.get('id') || null,
      title:         formData.title,
      display_types: displayTypes.length ? displayTypes : ['table'],
      vars:          formData.vars,
      inputMode:     formData.query_input_mode,
      labels,
      ...formData.query
    };

    this.setState({ saving: true });
    dispatch(saveReport(reportData)).then(() => {
      this.setState({ saving: false });
    });
  };

  doSubmit = () => {
    this.props.dispatch(submit('editStat'));
  };

  renderForm() {
    const saving = this.state.saving;
    const { groupParams, report } = this.props;

    const EditStatForm = reduxForm({
      form:          'editStat',
      initialValues: this.state.initialFormValue,
      onSubmit:      this.onSubmit,
    })(EditForm);

    const saveBtn = (<button
      type="button"
      onClick={this.doSubmit}
      className={classNames(
        'dp-button',
        'dp-button--l',
        'dp-button--primary',
        'dp-button--shape-default',
        { 'dp-button--loading': saving }
      )}
    >Save</button>);

    const controls = (
      <div style={{ textAlign: 'center', margin: '15px' }}>
        { report.get('is_custom')
          ? saveBtn
          : <em>You cannot edit a built-in report. If you want to change it, click the clone button above.</em> }
      </div>
    );

    return (<div>
      <EditStatForm groupParams={groupParams.toJS()} dpqlParser={EditContainer.dpqlParser} />
      {controls}
    </div>);
  }

  renderReport() {
    return (
      <div className="report-view edit">
        { this.props.report.get('id') ?
          <div className="title-bar">
            <div className="title">{this.props.report.get('title')}</div>
            <div className="ctrl">
              <Button type="secondary" size="medium" onClick={this.onRunClick}>
                <i className="fa fa-undo" /> Cancel
              </Button>
              <Button size="medium" onClick={this.onCloneClick}><i className="fa fa-clone" /> Clone</Button>
            </div>
          </div>
          : <div className="title-bar"><div className="title">New Report</div></div> }
        {this.renderForm()}
      </div>
    );
  }

  render() {
    const { report } = this.props;
    const reportExists  = report && report.get('id') && report.get('query_parts');
    return (
      <div className="report-widget-edit-n-run-content">
        { (reportExists || report.get('is_new')) ? this.renderReport() : <Loader size="xlarge" /> }
      </div>
    );
  }
}

export const Edit = connect()(EditContainer);
