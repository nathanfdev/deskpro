import React, { PropTypes } from 'react';
import Immutable from 'immutable';

class Edit extends React.Component {

  static propTypes = {
    report: PropTypes.object
  };

  constructor(props) {
    super(props);

    this.state = {
      report: Immutable.fromJS({ id: 0, is_new: false, is_custom: false })
    };
  }

  componentWillReceiveProps(newProps) {
    if (newProps.report && newProps.report.get('id') !== this.state.report.get('id')) {
      this.setState({ report: newProps.report });
    }
  }

  renderReport() {
    const { report } = this.state;

    return (
      <div className="reports-editor-panel full-editor">
        <div className="editor-form full-editor-form">
          <div className="inline-input">
            <div className="query-part title">
              <div>
                { report.get('title') }
              </div>
            </div>
          </div>
          <div className="inline-input">
            <label htmlFor="select">Select:</label>
            <div className="input-container">
              <div className="query-part">
                <div>
                  { report.get('query_parts') ? report.get('query_parts').select : '' }
                </div>
              </div>
            </div>
          </div>

          <div className="inline-input">
            <label htmlFor="from">From:</label>
            <div className="input-container">
              <div className="query-part short">
                <div>
                  { report.get('query_parts') ? report.get('query_parts').get('from') : '' }
                </div>
              </div>
            </div>
          </div>
          <div className="inline-input">
            <label htmlFor="where">Where:</label>
            <div className="input-container">
              <div className="query-part">
                <div>
                  { report.get('query_parts') ? report.get('query_parts').get('where') : '' }
                </div>
              </div>
            </div>
          </div>
          <div className="inline-input">
            <label htmlFor="splitBy">Split By:</label>
            <div className="input-container">
              <div className="query-part">
                <div>
                  { report.get('query_parts') ? report.get('query_parts').get('splitBy') : '' }
                </div>
              </div>
            </div>
          </div>
          <div className="inline-input">
            <label htmlFor="groupBy">Group By:</label>
            <div className="input-container">
              <div className="query-part">
                <div>
                  { report.get('query_parts') ? report.get('query_parts').get('groupBy') : '' }
                </div>
              </div>
            </div>
          </div>
          <div className="inline-input">
            <label htmlFor="orderBy">Order By:</label>
            <div className="input-container">
              <div className="query-part">
                <div>
                  { report.get('query_parts') ? report.get('query_parts').get('orderBy') : '' }
                </div>
              </div>
            </div>
          </div>
          <div className="inline-input">
            <label htmlFor="limit">Limit:</label>
            <div className="input-container">
              <input name="limit" type="text"  style={{ width: '100px' }} value={report.get('query_parts') ? report.get('query_parts').limit : ''} />
            </div>
            <div className="offset">
              <label htmlFor="offset">Offset:</label>
              <input name="offset" type="text" value={report.get('query_parts') ? report.get('query_parts').offset : ''} />
            </div>
          </div>

          <div className="widget-vars">
            <h3>Variables</h3>
            <div>
              <div className="inline-input">
                <label htmlFor="type" style={{ padding: '3px 10px' }}><i className="fa fa-times" /></label>
                <input name="type" />

                <select style={{ minWidth: '70px' }}>
                  <option value="dates">
                    Date
                  </option>
                  <option value="fields">
                    Group by field
                  </option>
                  <option value="statuses">
                    Status group
                  </option>
                  <option value="orders">
                    Order group
                  </option>
                </select>

                <select style={{ minWidth: '200px' }} />
                <select style={{ minWidth: '200px' }} />
                <select style={{ minWidth: '200px' }} />

              </div>
            </div>
            <i className="fa fa-plus-circle" /> Add new var
          </div>

          <div className="editor-controls">
            <a className="button">Save Query <i className="fa fa-save" /></a>
            <a className="button button-edit">Test <i className="fa fa-fast-forward" /></a>
          </div>
        </div>
      </div>
    );
  }

  render() {
    return (
      <div className="stat-large-preview-wrapper">
        { this.props.report ? this.renderReport() : '' }
      </div>
    );
  }
}

export default Edit;
