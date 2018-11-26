import React from 'react';
import SemanticSearchBox from 'DeskPRO/Component/Semantic/SearchBox';

class SearchBox extends React.Component {
  handleChange = (value) => {
    this.setState({
      value
    });
  };

  openSearch = () => {
    const event = new CustomEvent('dpLeftDrawer',
      {
        detail:
        {
          module: 'Search',
          style:  {
            height: 48
          }
        }
      }
    );
    window.document.dispatchEvent(event);
  };

  render() {
    const { ...props } = this.props;
    if (window.DP_HAS_NEW_SEARCH) {
      return (
        <SemanticSearchBox clear={false} {...props}>
          <div className="new-search" onClick={this.openSearch} />
        </SemanticSearchBox>
      );
    }
    return <SemanticSearchBox ref={(c) => { this.searchBox = c; }} {...props} />;
  }
}

export default SearchBox;
