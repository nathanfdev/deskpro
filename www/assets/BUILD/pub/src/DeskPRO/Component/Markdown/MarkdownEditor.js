import React, { PropTypes } from 'react';
import { TabGroup, Tab } from 'DeskPRO/Component/Semantic/Tab';
import MarkdownIt from 'markdown-it';

class MarkdownEditor extends React.Component {
  static propTypes = {
    value:    PropTypes.string,
    onChange: PropTypes.func
  };
  static defaultProps = {
    onChange() {}
  };

  constructor(props) {
    super(props);
    this.state = {
      html: ''
    };
  }

  onTabChange = (key) => {
    if (key === 'preview') {
      const md = new MarkdownIt({
        html:        false,
        linkify:     true,
        typographer: true
      });
      this.setState({ html: md.render(this.props.value) });
    }
  };

  render() {
    return (
      <div>
        <TabGroup onChange={this.onTabChange}>
          <Tab key="editor" label="First">
            <textarea rows="20" cols="100" value={this.props.value} onChange={this.props.onChange} />
          </Tab>
          <Tab key="preview" label="Preview">
            <div className="preview" dangerouslySetInnerHTML={{ __html: this.state.html }} />
          </Tab>
        </TabGroup>
      </div>
    );
  }
}
export default MarkdownEditor;
