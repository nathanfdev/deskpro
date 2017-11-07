import PropTypes from 'prop-types';
import React from 'react';
import BaseForm from 'DeskPRO/Component/Form/BaseForm';
import { Loader } from '@deskpro/react-components';
import { Input, Form, Field, MultiSelect, Textarea } from 'DeskPRO/Component/Semantic/ReactForm';
import { Fieldset, createValue } from '@deskpro/react-forms';
import classNames from 'classnames';
import Immutable from 'immutable';
import VarsField from './Fields/VarsField';
import LabelsField from './Fields/LabelsField';
import { displayTypes } from './helper';

class Edit extends BaseForm {

  static propTypes = {
    report:      PropTypes.object,
    labels:      PropTypes.object,
    groupParams: PropTypes.object,
    onRunClick:  PropTypes.func.isRequired,
  };

  constructor(props) {
    super(props);
    this.onRunClick    = this.onRunClick.bind(this);
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
    const { report } = props;
    if (report) {
      const queryParts = report.has('query_parts') ? report.get('query_parts') : Immutable.fromJS({});
      const state = {
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
        state.value.id = report.get('id');
      }
      this.setState({
        formData: createValue(state)
      });
    }
  }

  onRunClick() {
    this.props.onRunClick(this.props.report);
  }

  renderReport() {
    const { formData, saving } = this.state;
    const { groupParams, report, labels } = this.props;

    return (
      <div className="reports-editor-panel full-editor">
        <Form formValue={formData} className="editor-form full-editor-form" onSubmit={this.onSubmit}>
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
            <Field select="display_types" label="Available display types">
              <MultiSelect choices={displayTypes} />
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
          </Fieldset>
        </Form>
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
