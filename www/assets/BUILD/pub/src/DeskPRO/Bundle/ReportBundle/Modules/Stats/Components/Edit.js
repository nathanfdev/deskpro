import PropTypes from 'prop-types';
import React from 'react';
import { reduxForm } from 'redux-form';
import { Button, Loader } from '@deskpro/react-components';
import { Input, Form, Field, Textarea } from 'DeskPRO/Component/Semantic/ReactForm';
import classNames from 'classnames';
import Immutable from 'immutable';
import { EditForm } from './EditForm';

class Edit extends React.Component {

  static propTypes = {
    report:       PropTypes.object,
    labels:       PropTypes.object,
    groupParams:  PropTypes.object,
    onRunClick:   PropTypes.func.isRequired,
    onCloneClick: PropTypes.func.isRequired,
    parseQuery:   PropTypes.func.isRequired,
  };


  static buildQuery(parts) {
    if (!parts.select) {
      parts.select = 'COUNT()';
    }
    let query = `SELECT ${parts.select}\nFROM ${parts.from}`;
    if (parts.where) {
      query = `${query}\nWHERE ${parts.where}`;
    }
    if (parts.splitBy) {
      query = `${query}\nSPLIT BY ${parts.splitBy}`;
    }
    if (parts.groupBy) {
      query = `${query}\nGROUP BY ${parts.groupBy}`;
    }
    if (parts.withRollup) {
      query = `${query}\n WITH ROLLUP`;
    }
    if (parts.orderBy) {
      query = `${query}\nORDER BY ${parts.orderBy}`;
    }
    if (parts.limit) {
      query = `${query}\nLIMIT ${parts.limit}`;
    }
    if (parts.offset) {
      query = `${query} ${parts.offset}`;
    }

    return query;
  }

  constructor(props) {
    super(props);
    const { report }  = props;
    const queryParts  = report.has('query_parts') ? report.get('query_parts') : Immutable.fromJS({});
    this.onRunClick   = this.onRunClick.bind(this);
    this.onCloneClick = this.onCloneClick.bind(this);
  }

  getDefaultState() {
    const { report } = this.props;
    const queryParts = report.has('query_parts') ? report.get('query_parts') : Immutable.fromJS({});
    const state = {
      title:         report.get('title'),
      labels:        report.get('labels', Immutable.List()).toArray(),
      desc:          report.get('description', ''),
      display_types: report.get('display_types', Immutable.List()).toJS(),
      select:        queryParts.get('select', ''),
      from:          queryParts.get('from', ''),
      where:         queryParts.get('where', ''),
      splitBy:       queryParts.get('splitBy', ''),
      groupBy:       queryParts.get('groupBy', ''),
      orderBy:       queryParts.get('orderBy', ''),
      offset:        queryParts.get('offset', ''),
      limit:         queryParts.get('limit', ''),
      vars:          report.get('variables', Immutable.List()).toJS(),
    };

    if (report.get('id')) {
      state.id = report.get('id');
    }

    return state;
  }

  componentWillReceiveProps(props) {
    return;
    const { report } = props;
    if (report) {
      const queryParts = report.has('query_parts') ? report.get('query_parts') : Immutable.fromJS({});
      const formData = {
        value: {
          title:         report.get('title'),
          labels:        report.get('labels', Immutable.List()).toArray(),
          desc:          report.get('description', ''),
          display_types: report.get('display_types', Immutable.List()).toJS(),
          select:        queryParts.get('select', ''),
          from:          queryParts.get('from', ''),
          where:         queryParts.get('where', ''),
          splitBy:       queryParts.get('splitBy', ''),
          groupBy:       queryParts.get('groupBy', ''),
          orderBy:       queryParts.get('orderBy', ''),
          offset:        queryParts.get('offset', ''),
          limit:         queryParts.get('limit', ''),
          vars:          report.get('variables', Immutable.List()).toJS(),
        },
        errorList: {},
        onChange:  this.onChange
      };
      if (report.get('id')) {
        formData.value.id = report.get('id');
      }

      // TODO

      const state = {
        formData: formData,
        query:    Edit.buildQuery(queryParts.toJS()),
      };

      if (report.get('id') !== this.props.report.get('id')) {
        state.mode = 'form';
      }

      this.setState(state);
    }
  }

  onRunClick(event) {
    event.preventDefault();
    event.stopPropagation();
    this.props.onRunClick(this.props.report);
  }

  onCloneClick(event) {
    event.preventDefault();
    event.stopPropagation();
    this.props.onCloneClick(this.props.report);
  }

  renderForm() {
    const saving = false;
    const { groupParams, report } = this.props;

    const EditStateForm = reduxForm({
      form: 'editStat'
    })(EditForm);

    return (<div>
      <EditStateForm groupParams={groupParams} />

      { report.get('is_custom') ? <button className={classNames('ui button', { loading: saving })}>Save</button> : null }
      <button onClick={this.onRunClick} className={classNames('ui olive button', { loading: saving })}>Run</button>
      <button onClick={this.onCloneClick} className={classNames('ui orange button', { loading: saving })}>Clone</button>
    </div>);
  }

  renderReport() {
    return (
      <div className="report-view edit">
        <div className="title-bar">
          <div className="title">{this.props.report.get('title')}</div>
          <div className="ctrl">
            <Button type="secondary" size="medium" onClick={this.onRunClick.bind(this)}><i className="fa fa-undo"></i> Cancel</Button>
          </div>
        </div>
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

export default Edit;
