import PropTypes from 'prop-types';
import React from 'react';
import BaseForm from 'DeskPRO/Component/Form/BaseForm';
import { Loader } from '@deskpro/react-components';
import Button from '@deskpro/react-components/lib/Components/Buttons/Button';
import { Input, Form, Field, Textarea } from 'DeskPRO/Component/Semantic/ReactForm';
import { Fieldset, createValue } from '@deskpro/react-forms';
import classNames from 'classnames';
import Immutable from 'immutable';
import VarsField from './Fields/VarsField';
import LabelsField from './Fields/LabelsField';

class Edit extends BaseForm {

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
    this.state.mode   = 'form';
    const { report }  = props;
    const queryParts  = report.has('query_parts') ? report.get('query_parts') : Immutable.fromJS({});
    this.state.query  = Edit.buildQuery(queryParts.toJS());
    this.onRunClick   = this.onRunClick.bind(this);
    this.onCloneClick = this.onCloneClick.bind(this);
    this.switchToForm = this.switchToForm.bind(this);
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

  switchToForm() {
    const { parseQuery, report } = this.props;
    const { query } = this.state;
    parseQuery(report, query);
    this.setState({ mode: 'form' });
  }

  componentWillReceiveProps(props) {
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

      const state = {
        formData: createValue(formData),
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
    const { formData, saving } = this.state;
    const { groupParams, report, labels } = this.props;

    return (<Form formValue={formData} className="editor-form full-editor-form" onSubmit={this.onSubmit}>
      <button onClick={() => this.setState({ mode: 'query' })} className={classNames('ui olive button', { loading: saving })}>Show Query</button>
      <Fieldset>
        <Field select="title" label="Title">
          <Input type="text" />
        </Field>
        <Field select="labels"  label="Labels">
          <LabelsField labels={labels} />
        </Field>
        <Field select="desc" label="Provide short description for this report">
          <Textarea className="report-description" />
        </Field>
        <Field select="select" label="Select">
          <Input type="text" />
        </Field>
        <Field select="from" label="From">
          <Input type="text" />
        </Field>
        <Field select="where" label="Where">
          <Input type="text" />
        </Field>
        <Field select="splitBy" label="Split By">
          <Input type="text" />
        </Field>
        <Field select="groupBy" label="Group By">
          <Input type="text" />
        </Field>
        <Field select="orderBy" label="Order By">
          <Input type="text" />
        </Field>
        <Field select="limit" label="Limit">
          <Input type="text" />
        </Field>
        <Field select="offset" label="Offset">
          <Input type="text" />
        </Field>

        <Field select="vars" label="Vars" className="vars">
          <VarsField loading={saving} groupParams={groupParams} />
        </Field>
        <br />
        <br />
        { report.get('is_custom') ? <button className={classNames('ui button', { loading: saving })}>Save</button> : null }
        <button onClick={this.onRunClick} className={classNames('ui olive button', { loading: saving })}>Run</button>
        <button onClick={this.onCloneClick} className={classNames('ui orange button', { loading: saving })}>Clone</button>
      </Fieldset>
    </Form>);
  }

  renderQuery() {
    const { saving } = this.state;

    return (
      <div className="editor-form full-editor-form">
        <button onClick={this.switchToForm} className={classNames('ui olive button', { loading: saving })}>Show Form</button>
        <Textarea className="report-query" value={this.state.query} onChange={(query) => { this.setState({ query }); }} />
      </div>
    );
  }

  renderReport() {
    return (
      <div className="report-view edit">
        <div className="title-bar">
          <div className="title">{this.state.formData.value.title}</div>
          <div className="ctrl">
            <Button type="secondary" size="medium" onClick={this.onRunClick.bind(this)}><i className="fa fa-undo"></i> Cancel</Button>
          </div>
        </div>
        {this.state.mode === 'form' ? this.renderForm() : this.renderQuery()}
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
